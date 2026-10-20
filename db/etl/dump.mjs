import { readFileSync } from 'node:fs';
import { gunzipSync } from 'node:zlib';

/**
 * Parser minimal untuk dump `mysqldump --skip-extended-insert` maupun
 * extended insert. Cukup untuk dump yang dihasilkan proyek ini.
 */
export function readDump(file) {
    const raw = readFileSync(file);
    const sql = file.endsWith('.gz') ? gunzipSync(raw).toString('utf8') : raw.toString('utf8');

    const tables = new Map();

    for (const match of sql.matchAll(/CREATE TABLE `([^`]+)` \(([\s\S]*?)\n\) ENGINE/g)) {
        const [, name, body] = match;
        const columns = [];

        for (const line of body.split('\n')) {
            const column = line.match(/^\s*`([^`]+)`\s+/);
            if (column) columns.push(column[1]);
        }

        tables.set(name, { columns, rows: [] });
    }

    for (const match of sql.matchAll(/INSERT INTO `([^`]+)` VALUES/g)) {
        const [, name] = match;
        const table = tables.get(name);

        if (!table) {
            throw new Error(`INSERT untuk tabel tanpa CREATE TABLE: ${name}`);
        }

        const end = findStatementEnd(sql, match.index + match[0].length);
        const rows = parseValueList(sql.slice(match.index + match[0].length, end));

        for (const row of rows) {
            if (row.length !== table.columns.length) {
                throw new Error(
                    `Jumlah kolom ${name} tidak cocok: ${row.length} vs ${table.columns.length}`,
                );
            }

            table.rows.push(Object.fromEntries(table.columns.map((column, index) => [column, row[index]])));
        }
    }

    return tables;
}

function findStatementEnd(sql, start) {
    let quoted = false;

    for (let index = start; index < sql.length; index += 1) {
        const char = sql[index];

        if (quoted) {
            if (char === '\\') {
                index += 1;
                continue;
            }

            if (char === "'") {
                if (sql[index + 1] === "'") {
                    index += 1;
                    continue;
                }

                quoted = false;
            }

            continue;
        }

        if (char === "'") {
            quoted = true;
            continue;
        }

        if (char === ';') return index;
    }

    throw new Error('Statement INSERT tidak ditemukan akhirnya.');
}

function parseValueList(text) {
    const rows = [];
    let index = 0;

    while (index < text.length) {
        while (index < text.length && /[\s,]/.test(text[index])) index += 1;
        if (index >= text.length) break;

        if (text[index] !== '(') {
            throw new Error(`Nilai tidak valid pada offset ${index}: ${text.slice(index, index + 40)}`);
        }

        index += 1;
        const row = [];
        let field = '';
        let fieldQuoted = false;
        let quoted = false;

        while (index < text.length) {
            const char = text[index];

            if (quoted) {
                if (char === '\\') {
                    const escaped = text[index + 1] ?? '';
                    const map = {
                        0: '\0',
                        b: '\b',
                        n: '\n',
                        r: '\r',
                        t: '\t',
                        Z: '\x1a',
                        '\\': '\\',
                        "'": "'",
                        '"': '"',
                    };

                    field += map[escaped] ?? escaped;
                    index += 2;
                    continue;
                }

                if (char === "'") {
                    if (text[index + 1] === "'") {
                        field += "'";
                        index += 2;
                        continue;
                    }

                    quoted = false;
                    index += 1;
                    continue;
                }

                field += char;
                index += 1;
                continue;
            }

            if (char === "'") {
                quoted = true;
                fieldQuoted = true;
                index += 1;
                continue;
            }

            if (char === ',') {
                row.push(fieldQuoted ? field : unquotedValue(field));
                field = '';
                fieldQuoted = false;
                index += 1;
                continue;
            }

            if (char === ')') {
                row.push(fieldQuoted ? field : unquotedValue(field));
                rows.push(row);
                index += 1;
                break;
            }

            if (!/\s/.test(char)) field += char;
            index += 1;
        }
    }

    return rows;
}

function unquotedValue(value) {
    return value.toUpperCase() === 'NULL' ? null : value;
}
