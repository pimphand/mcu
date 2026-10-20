import { readFileSync } from 'node:fs';
import { createConnection } from 'mysql2/promise';
import pg from 'pg';
import { readDump } from './dump.mjs';
import {
    convertValue,
    dedupeBy,
    dedupeOneToOne,
    normalizeGender,
    resolveClientId,
} from './transform.mjs';

const { Client } = pg;

const TABLES = [
    { source: 'clients', target: 'clients', deferAuditors: true },
    { source: 'users', target: 'users', aliases: { password_hash: 'password' } },
    { source: 'contracts', target: 'contracts' },
    { source: 'departments', target: 'departments' },
    { source: 'divisis', target: 'divisis' },
    { source: 'employees', target: 'employees' },
    { source: 'participants', target: 'participants' },
    { source: 'tanda_vitals', target: 'tanda_vitals' },
    { source: 'pemeriksaan_fisiks', target: 'pemeriksaan_fisiks' },
    { source: 'laboratoria', target: 'laboratoria' },
    { source: 'radiologis', target: 'radiologis' },
    { source: 'spirometris', target: 'spirometris' },
    { source: 'audiometris', target: 'audiometris' },
    { source: 'rectals', target: 'rectals' },
    { source: 'ekgs', target: 'ekgs' },
    { source: 'validate_doctors', target: 'validate_doctors' },
    { source: 'general_results', target: 'general_results' },
];

const DELETE_ORDER = [
    'general_results',
    'validate_doctors',
    'ekgs',
    'rectals',
    'audiometris',
    'spirometris',
    'radiologis',
    'laboratoria',
    'pemeriksaan_fisiks',
    'tanda_vitals',
    'participants',
    'employees',
    'divisis',
    'departments',
    'contracts',
    'users',
    'clients',
];

const ONE_TO_ONE = new Set([
    'tanda_vitals',
    'pemeriksaan_fisiks',
    'laboratoria',
    'radiologis',
    'spirometris',
    'audiometris',
    'rectals',
    'ekgs',
    'general_results',
]);

/**
 * Opsi koneksi MySQL untuk mode `--mysql`/`MYSQL_*`.
 *
 * `dateStrings: true` wajib: tanpa itu mysql2 mengubah DATETIME/DATE menjadi
 * objek Date pada timezone mesin, sehingga nilai legacy bergeser mengikuti TZ
 * komputer yang menjalankan ETL dan hasilnya berbeda dari mode `--dump` (yang
 * membaca nilai apa adanya). Dengan ini kedua mode menghasilkan nilai identik.
 * `charset` utf8mb4 menjaga karakter 4 byte pada nama/alamat.
 */
const MYSQL_OPTIONS = {
    dateStrings: true,
    charset: 'utf8mb4',
    supportBigNumbers: true,
    timezone: 'Z',
};

const args = parseArgs(process.argv.slice(2));

if (args.help) {
    console.log(`Usage:
  node migrate.mjs --dump "db/12 oktober....sql.gz" --truncate
  node migrate.mjs --mysql "mysql://user:pass@127.0.0.1:3306/medical_check_up" --truncate

Mode MySQL membaca nilai tanggal sebagai teks (dateStrings) supaya hasilnya
identik dengan mode dump, terlepas dari timezone mesin yang menjalankan ETL.

Environment:
  DATABASE_URL   PostgreSQL tujuan (atau PGHOST/PGPORT/PGUSER/PGPASSWORD/PGDATABASE)
  MYSQL_URL      Alternatif sumber MySQL (atau MYSQL_HOST/MYSQL_PORT/...)
  LEGACY_DUMP    Alternatif path dump bila tidak memakai argumen

Flag:
  --truncate     Hapus data bisnis tujuan sebelum import (roles/menus tetap)
  --dry-run      Baca dan validasi tanpa menulis
`);
    process.exit(0);
}

const databaseUrl = process.env.DATABASE_URL;
const pgConfig = databaseUrl
    ? pgConfigFromUrl(databaseUrl)
    : {
          host: process.env.PGHOST ?? '127.0.0.1',
          port: Number(process.env.PGPORT ?? 5432),
          user: process.env.PGUSER ?? 'mcu_admin',
          password: process.env.PGPASSWORD ?? '',
          database: process.env.PGDATABASE ?? 'mcu',
          ssl: sslOptions(process.env.PGSSLMODE),
      };

if (!databaseUrl && !process.env.PGDATABASE && !process.env.PGUSER) {
    fail('DATABASE_URL atau PGDATABASE/PGUSER wajib diisi.');
}

const dumpPath = args.dump ?? process.env.LEGACY_DUMP;
// URL eksplisit dipakai apa adanya; kalau tidak ada, opsi dirakit dari MYSQL_*.
const mysqlUrlArg = args.mysql ?? process.env.MYSQL_URL ?? null;
const mysqlUrl = mysqlUrlArg ?? mysqlUrlFromEnv();

if (!dumpPath && !mysqlUrl) {
    fail('Isi --dump / LEGACY_DUMP atau --mysql / MYSQL_URL/MYSQL_HOST.');
}

const pgClient = new Client(pgConfig);
await pgClient.connect();

try {
    const source = await loadSource({ dumpPath, mysqlUrl, hasMysqlUrl: Boolean(mysqlUrlArg) });
    const meta = await loadTargetMeta(pgClient);

    const clientRows = source.rows('clients');
    const context = {
        clientByCode: new Map(clientRows.map((row) => [String(row.code), Number(row.id)])),
        clientIds: new Set(clientRows.map((row) => Number(row.id))),
    };

    const report = { loaded: new Map(), skipped: 0, duplicates: new Map() };

    if (args.truncate && !args.dryRun) {
        await pgClient.query('begin');
        await pgClient.query("set local time zone 'UTC'");

        for (const table of DELETE_ORDER) {
            await pgClient.query(`delete from ${quote(table)}`);
        }
    } else if (!args.dryRun) {
        await pgClient.query('begin');
        await pgClient.query("set local time zone 'UTC'");
    }

    for (const config of TABLES) {
        let rows = source.rows(config.source);

        if (ONE_TO_ONE.has(config.target)) {
            const before = rows.length;
            rows = dedupeOneToOne(rows, 'participant_id', report);
            report.duplicates.set(config.target, before - rows.length);
        }

        if (config.target === 'validate_doctors') {
            const before = rows.length;
            rows = dedupeBy(
                rows,
                (row) =>
                    row.no_mcu
                        ? `${row.client_id ?? ''}|${row.no_mcu}`
                        : `id:${row.id}`,
                report,
            );
            report.duplicates.set(config.target, before - rows.length);
        }

        if (config.target === 'users') {
            rows = dedupeBy(rows, (row) => row.username ?? `id:${row.id}`, report);
        }

        const columns = buildColumns(config, meta.get(config.target) ?? [], source.columns(config.source));

        if (columns.length === 0) {
            throw new Error(`Tidak ada kolom yang bisa dipetakan untuk ${config.target}.`);
        }

        if (config.target === 'clients') {
            // created_by/updated_by diisi setelah users masuk (ada siklus FK).
            const index = columns.findIndex((column) => column.name === 'created_by');
            if (index >= 0) {
                columns.splice(
                    columns.findIndex((column) => column.name === 'created_by'),
                    1,
                );
            }
            const updatedIndex = columns.findIndex((column) => column.name === 'updated_by');
            if (updatedIndex >= 0) columns.splice(updatedIndex, 1);
        }

        report.loaded.set(config.target, 0);

        for (const row of rows) {
            try {
                const values = columns.map((column) => {
                    const sourceKey = config.aliases?.[column.name] ?? column.name;
                    const raw = row[sourceKey];

                    if (column.name === 'gender' && config.target === 'participants' && !raw) {
                        throw new Error('gender peserta kosong.');
                    }

                    const value = convertValue(
                        config.target,
                        column.name,
                        column.type,
                        raw,
                        context,
                    );

                    // Beberapa baris legacy (mis. user admin lama) tidak punya
                    // timestamp; kolom baru NOT NULL, jadi isi waktu migrasi.
                    if (
                        value === null &&
                        (column.name === 'created_at' || column.name === 'updated_at')
                    ) {
                        return new Date().toISOString();
                    }

                    return value;
                });

                if (config.target === 'participants' && values[columns.findIndex((c) => c.name === 'gender')] === null) {
                    throw new Error('gender peserta kosong.');
                }

                if (!args.dryRun) {
                    await pgClient.query(insertSql(config.target, columns, args.truncate), values);
                }

                report.loaded.set(config.target, (report.loaded.get(config.target) ?? 0) + 1);
            } catch (error) {
                throw new Error(
                    `Gagal memproses ${config.target} id=${row.id ?? '?'}: ${error.message}`,
                );
            }
        }

        if (config.target === 'validate_doctors') {
            // Validasi client_id memakai resolver; ini memastikan pesan error jelas.
            for (const row of rows) {
                if (row.client_id) resolveClientId(row.client_id, context);
                if (row.gender) normalizeGender(row.gender);
            }
        }
    }

    if (!args.dryRun) {
        await restoreClientAuditors(pgClient, clientRows);
        await resetSequences(pgClient);
        await pgClient.query('commit');
    }

    await printReport(pgClient, source, report, args.dryRun);
} catch (error) {
    try {
        await pgClient.query('rollback');
    } catch {
        // transaksi mungkin belum dimulai
    }

    fail(error.stack ?? error.message);
} finally {
    await pgClient.end().catch(() => {});
}

/**
 * pg-connection-string memperlakukan `sslmode=require` sebagai `verify-full`,
 * sehingga koneksi ke PostgreSQL terkelola (Supabase) gagal dengan
 * "self-signed certificate in certificate chain". Jadi `sslmode` dilepas dari
 * URL dan TLS diatur eksplisit: require = terenkripsi tanpa verifikasi CA
 * (semantik libpq), verify-full/verify-ca = verifikasi penuh.
 */
function pgConfigFromUrl(rawUrl) {
    const url = new URL(rawUrl);
    const mode = url.searchParams.get('sslmode') ?? process.env.PGSSLMODE ?? '';
    url.searchParams.delete('sslmode');

    return { connectionString: url.toString(), ssl: sslOptions(mode) };
}

function sslOptions(mode) {
    if (!mode) return undefined;

    if (mode === 'disable' || mode === 'allow') return false;
    if (mode === 'prefer' || mode === 'require' || mode === 'no-verify') {
        return { rejectUnauthorized: false };
    }

    if (mode === 'verify-ca' || mode === 'verify-full') {
        const caPath = process.env.PGSSLROOTCERT;
        return {
            rejectUnauthorized: true,
            ...(caPath ? { ca: readFileSync(caPath, 'utf8') } : {}),
        };
    }

    throw new Error(`sslmode tidak dikenal: ${mode}`);
}

function mysqlUrlFromEnv() {
    if (!process.env.MYSQL_HOST && !process.env.MYSQL_USER && !process.env.MYSQL_DATABASE) {
        return null;
    }

    const url = new URL('mysql://localhost');
    url.username = process.env.MYSQL_USER ?? 'mcu';
    url.password = process.env.MYSQL_PASSWORD ?? '';
    url.hostname = process.env.MYSQL_HOST ?? '127.0.0.1';
    url.port = process.env.MYSQL_PORT ?? '3306';
    url.pathname = `/${process.env.MYSQL_DATABASE ?? 'medical_check_up'}`;

    return url.toString();
}

function parseArgs(argv) {
    const parsed = {};

    for (let index = 0; index < argv.length; index += 1) {
        const arg = argv[index];

        if (arg === '--truncate') parsed.truncate = true;
        else if (arg === '--dry-run') parsed.dryRun = true;
        else if (arg === '--help' || arg === '-h') parsed.help = true;
        else if (arg.startsWith('--dump=')) parsed.dump = arg.slice('--dump='.length);
        else if (arg.startsWith('--mysql=')) parsed.mysql = arg.slice('--mysql='.length);
        else if (arg === '--dump') parsed.dump = argv[++index];
        else if (arg === '--mysql') parsed.mysql = argv[++index];
        else fail(`Argumen tidak dikenal: ${arg}`);
    }

    return parsed;
}

async function loadSource({ dumpPath, mysqlUrl, hasMysqlUrl }) {
    if (dumpPath) {
        const tables = readDump(dumpPath);

        return {
            mode: `dump ${dumpPath}`,
            rows: (name) => tables.get(name)?.rows ?? [],
            columns: (name) => tables.get(name)?.columns ?? [],
        };
    }

    const connection = await createConnection(
        hasMysqlUrl
            ? { ...MYSQL_OPTIONS, uri: mysqlUrl }
            : {
                  ...MYSQL_OPTIONS,
                  host: process.env.MYSQL_HOST ?? '127.0.0.1',
                  port: Number(process.env.MYSQL_PORT ?? 3306),
                  user: process.env.MYSQL_USER ?? 'mcu',
                  password: process.env.MYSQL_PASSWORD ?? '',
                  database: process.env.MYSQL_DATABASE ?? 'medical_check_up',
              },
    );

    const tables = new Map();

    try {
        for (const config of TABLES) {
            const [rows] = await connection.query(`select * from \`${config.source}\``);
            tables.set(config.source, rows);
        }
    } finally {
        await connection.end();
    }

    return {
        mode: `mysql ${mysqlUrl.replace(/:\/\/([^:]+):[^@]+@/, '://$1:***@')}`,
        rows: (name) => tables.get(name) ?? [],
        columns: (name) => (tables.get(name)?.[0] ? Object.keys(tables.get(name)[0]) : []),
    };
}

async function loadTargetMeta(client) {
    const names = TABLES.map((table) => table.target);
    const { rows } = await client.query(
        `select table_name, column_name, data_type
           from information_schema.columns
          where table_schema = 'public'
            and table_name = any($1::text[])
          order by table_name, ordinal_position`,
        [names],
    );

    const meta = new Map();

    for (const row of rows) {
        if (!meta.has(row.table_name)) meta.set(row.table_name, []);
        meta.get(row.table_name).push({ name: row.column_name, type: row.data_type });
    }

    return meta;
}

function buildColumns(config, targetColumns, sourceColumns) {
    const sourceSet = new Set(sourceColumns);

    return targetColumns.filter((column) => {
        if (column.name === 'id') return true;
        if (config.aliases?.[column.name]) return true;
        return sourceSet.has(column.name);
    });
}

function insertSql(target, columns, truncate) {
    const names = columns.map((column) => quote(column.name));
    const placeholders = columns.map((_, index) => `$${index + 1}`);
    const updates = columns
        .filter((column) => column.name !== 'id')
        .map((column) => `${quote(column.name)} = excluded.${quote(column.name)}`);

    const conflict = truncate || updates.length === 0
        ? ''
        : ` on conflict (id) do update set ${updates.join(', ')}`;

    return `insert into ${quote(target)} (${names.join(', ')}) values (${placeholders.join(', ')})${conflict}`;
}

async function restoreClientAuditors(client, clientRows) {
    for (const row of clientRows) {
        if (!row.created_by && !row.updated_by) continue;

        await client.query(
            'update public.clients set created_by = $1, updated_by = $2 where id = $3',
            [
                row.created_by ? Number(row.created_by) : null,
                row.updated_by ? Number(row.updated_by) : null,
                Number(row.id),
            ],
        );
    }
}

async function resetSequences(client) {
    for (const table of TABLES) {
        const { rows } = await client.query(`select coalesce(max(id), 0)::bigint as max_id from ${quote(table.target)}`);
        const max = Number(rows[0].max_id);

        await client.query('select setval(pg_get_serial_sequence($1, $2), $3, $4)', [
            `public.${table.target}`,
            'id',
            max > 0 ? max : 1,
            max > 0,
        ]);
    }

    const participant = await client.query(
        "select coalesce(max((regexp_match(code, '(\\d+)$'))[1]::bigint), 0) as max from public.participants",
    );
    const employee = await client.query(
        "select coalesce(max((regexp_match(code, '(\\d+)$'))[1]::bigint), 0) as max from public.employees where code is not null",
    );
    const registers = await client.query(
        'select count(*)::bigint as count from public.participants where register_number is not null',
    );

    await setSequence(client, 'participant_code_seq', Number(participant.rows[0].max));
    await setSequence(client, 'employee_code_seq', Number(employee.rows[0].max));
    await setSequence(client, 'register_number_seq', Number(registers.rows[0].count));
}

async function setSequence(client, sequence, max) {
    await client.query('select setval($1, $2, $3)', [
        `public.${sequence}`,
        Math.max(max, 1),
        max > 0,
    ]);
}

async function printReport(client, source, report, dryRun) {
    console.log(`Sumber: ${source.mode}${dryRun ? ' (dry-run)' : ''}`);
    console.log('table                     source  loaded  duplicate');
    console.log('----------------------------------------------------');

    for (const config of TABLES) {
        const sourceCount = source.rows(config.source).length;
        const loaded = report.loaded.get(config.target) ?? 0;
        const duplicate = report.duplicates.get(config.target) ?? 0;

        console.log(
            `${config.target.padEnd(25)} ${String(sourceCount).padStart(6)} ${String(loaded).padStart(7)} ${String(duplicate).padStart(10)}`,
        );
    }

    if (!dryRun) {
        const checks = await client.query(`
            select
              (select count(*) from public.participants)::int as participants,
              (select count(*) from public.participants where gender not in ('L', 'P'))::int as invalid_gender,
              (select count(*) from api.v_participant_list)::int as participant_list,
              (select count(*) from api.v_mcu_progress)::int as mcu_progress,
              (select count(*) from public.tanda_vitals where tekanan_darah is not null)::int as blood_pressure,
              (select count(*) from public.laboratoria)::int as lab_results,
              (select count(*) from public.radiologis)::int as radiology_results
        `);

        console.log('\nVerifikasi:');
        console.log(checks.rows[0]);
    }
}

function quote(identifier) {
    return `"${String(identifier).replaceAll('"', '""')}"`;
}

function fail(message) {
    console.error(`\nERROR: ${message}`);
    process.exit(1);
}
