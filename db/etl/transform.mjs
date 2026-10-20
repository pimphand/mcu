const GENDER_MAP = new Map([
    ['l', 'L'],
    ['lakilaki', 'L'],
    ['pria', 'L'],
    ['male', 'L'],
    ['p', 'P'],
    ['perempuan', 'P'],
    ['wanita', 'P'],
    ['female', 'P'],
]);

const IDENTIFIER_COLUMNS = new Set(['code', 'nik', 'username', 'email', 'no_mcu', 'mcu_id']);

export function normalizeGender(value) {
    if (value === null || value === undefined || value === '') return null;

    const normalized = GENDER_MAP.get(
        String(value)
            .trim()
            .toLowerCase()
            .replace(/[\s-]+/g, ''),
    );

    if (!normalized) {
        throw new Error(`Gender tidak dikenal: ${String(value)}`);
    }

    return normalized;
}

export function toBoolean(value) {
    if (value === null || value === undefined || value === '') return null;
    if (typeof value === 'boolean') return value;

    return value === 1 || value === '1' || value === 'true';
}

export function toInteger(value) {
    if (value === null || value === undefined || value === '') return null;

    const number = Number(value);

    if (!Number.isSafeInteger(number)) {
        throw new Error(`Angka tidak valid: ${String(value)}`);
    }

    return number;
}

export function toText(value) {
    if (value === null || value === undefined) return null;

    const text = String(value);
    return text === '' ? null : text;
}

export function toIdentifier(value) {
    const text = toText(value);
    return text === null ? null : text.trim();
}

export function toDate(value) {
    if (value === null || value === undefined || value === '') return null;

    const text = value instanceof Date ? value.toISOString().slice(0, 10) : String(value).slice(0, 10);

    if (text.startsWith('0000-00-00')) return null;

    return text;
}

export function toTimestamp(value) {
    if (value === null || value === undefined || value === '') return null;
    if (value instanceof Date) return value.toISOString();

    const text = String(value);
    if (text.startsWith('0000-00-00')) return null;

    return text;
}

export function toPassword(value) {
    const text = toText(value);

    if (!text) {
        throw new Error('Password legacy kosong; akun tidak bisa dimigrasikan.');
    }

    // Laravel memakai $2y$; bcryptjs menerima $2a$ untuk hash yang sama.
    return text.replace(/^\$2y\$/, '$2a$');
}

export function convertValue(table, column, type, value, context) {
    if (value === null || value === undefined) return null;

    if (column === 'password_hash') return toPassword(value);
    if (column === 'gender') return normalizeGender(value);
    if (IDENTIFIER_COLUMNS.has(column)) return toIdentifier(value);

    if (table === 'validate_doctors' && column === 'client_id') {
        return resolveClientId(value, context);
    }

    if (table === 'general_results' && column === 'client_id') {
        return resolveClientId(value, context);
    }

    if (type === 'boolean') return toBoolean(value);
    if (['smallint', 'integer', 'bigint'].includes(type)) return toInteger(value);
    if (type === 'date') return toDate(value);
    if (type === 'timestamp with time zone' || type === 'timestamp without time zone') {
        return toTimestamp(value);
    }

    return toText(value);
}

export function resolveClientId(value, context) {
    if (value === null || value === undefined || value === '') {
        throw new Error('client_id legacy kosong.');
    }

    const text = String(value).trim();

    if (context.clientByCode.has(text)) return context.clientByCode.get(text);

    const numeric = Number(text);
    if (Number.isSafeInteger(numeric) && context.clientIds.has(numeric)) return numeric;

    throw new Error(`client_id legacy tidak dikenal: ${text}`);
}

/**
 * Tabel hasil bersifat satu baris per peserta. Legacy menyimpan beberapa
 * duplikat; pertahankan baris dengan updated_at terbaru (fallback id).
 */
export function dedupeOneToOne(rows, keyName, report) {
    return dedupeBy(rows, (row) => row[keyName], report);
}

export function dedupeBy(rows, keyFn, report) {
    const byKey = new Map();
    let skipped = 0;

    for (const row of rows) {
        const key = keyFn(row);

        if (key === null || key === undefined || key === '') {
            skipped += 1;
            continue;
        }

        const current = byKey.get(key);

        if (!current) {
            byKey.set(key, row);
            continue;
        }

        skipped += 1;
        const currentStamp = `${current.updated_at ?? ''}|${String(current.id).padStart(12, '0')}`;
        const nextStamp = `${row.updated_at ?? ''}|${String(row.id).padStart(12, '0')}`;

        if (nextStamp > currentStamp) {
            byKey.set(key, row);
        }
    }

    if (skipped > 0) report.skipped += skipped;

    return [...byKey.values()];
}
