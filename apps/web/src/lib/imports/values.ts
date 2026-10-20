/**
 * Pembacaan nilai sel spreadsheet untuk import hasil. File Excel dari lab
 * sering mencampur teks, angka, dan tanggal asli Excel, jadi konversinya
 * dipusatkan di sini dan diuji unit.
 */

const EXCEL_EPOCH_UTC = Date.UTC(1899, 11, 30);
const DAY_MS = 86_400_000;
const MAX_EXCEL_SERIAL = 100_000;

const MONTHS: Record<string, number> = {
    jan: 1,
    feb: 2,
    mar: 3,
    apr: 4,
    mei: 5,
    may: 5,
    jun: 6,
    jul: 7,
    agu: 8,
    aug: 8,
    sep: 9,
    okt: 10,
    oct: 10,
    nov: 11,
    des: 12,
    dec: 12,
};

function pad(value: number): string {
    return value.toString().padStart(2, '0');
}

function isRecord(value: unknown): value is Record<string, unknown> {
    return typeof value === 'object' && value !== null;
}

/**
 * ExcelJS menyimpan sel rumus sebagai `{ formula, result }` dan sel kaya teks
 * sebagai `{ richText: [...] }`. Keduanya dibuka supaya isi file tidak hilang.
 */
function unwrap(value: unknown): unknown {
    if (!isRecord(value)) {
        return value;
    }

    if ('result' in value) {
        return unwrap(value.result);
    }

    if (Array.isArray(value.richText)) {
        return value.richText
            .map((part) => (isRecord(part) && typeof part.text === 'string' ? part.text : ''))
            .join('');
    }

    if (value instanceof Date) {
        return value;
    }

    return value;
}

export function toIsoDate(year: number, month: number, day: number): string | null {
    if (month < 1 || month > 12 || day < 1 || day > 31 || year < 1900) {
        return null;
    }

    const date = new Date(Date.UTC(year, month - 1, day));

    if (
        date.getUTCFullYear() !== year ||
        date.getUTCMonth() !== month - 1 ||
        date.getUTCDate() !== day
    ) {
        return null;
    }

    return `${year.toString().padStart(4, '0')}-${pad(month)}-${pad(day)}`;
}

function fromDate(value: Date): string | null {
    // ExcelJS membentuk Date dari serial Excel memakai UTC, jadi komponen UTC
    // yang benar. Date yang dibuat pengguna di zona WIB bisa bergeser sehari,
    // karena itu kolom tanggal sebaiknya diformat sebagai teks (template kita
    // memakai teks `dd-MMM-yy`).
    return toIsoDate(value.getUTCFullYear(), value.getUTCMonth() + 1, value.getUTCDate());
}

function fromSerial(serial: number): string | null {
    if (!Number.isFinite(serial) || serial < 1 || serial > MAX_EXCEL_SERIAL) {
        return null;
    }

    return fromDate(new Date(EXCEL_EPOCH_UTC + Math.round(serial * DAY_MS)));
}

function fromText(raw: string): string | null {
    const text = raw.trim();

    if (!text) {
        return null;
    }

    const iso = text.match(/^(\d{4})-(\d{1,2})-(\d{1,2})(?:[T\s].*)?$/);

    if (iso) {
        return toIsoDate(Number(iso[1]), Number(iso[2]), Number(iso[3]));
    }

    const compact = text.match(/^(\d{4})(\d{2})(\d{2})$/);

    if (compact) {
        return toIsoDate(Number(compact[1]), Number(compact[2]), Number(compact[3]));
    }

    // Serial Excel yang tersimpan sebagai teks ("45678").
    if (/^\d+(?:\.\d+)?$/.test(text)) {
        return fromSerial(Number(text));
    }

    const parts = text.split(/[\s/.-]+/).filter(Boolean);

    if (parts.length < 3) {
        return null;
    }

    const [first, second, third] = parts;

    if (!first || !second || !third) {
        return null;
    }

    // Format ISO dengan pemisah lain: 2024/10/07 atau 2024.10.07.
    if (/^\d{4}$/.test(first)) {
        return toIsoDate(Number(first), Number(second), Number(third));
    }

    const year = Number(third.length === 2 ? `20${third}` : third);

    if (second && /^[a-z]{3,}$/i.test(second)) {
        const month = MONTHS[second.slice(0, 3).toLowerCase()];

        return month ? toIsoDate(year, month, Number(first)) : null;
    }

    const a = Number(first);
    const b = Number(second);

    if (!Number.isInteger(a) || !Number.isInteger(b)) {
        return null;
    }

    // Indonesia memakai dd/mm/yyyy. Bila angka pertama > 12 jelas tanggal,
    // dan bila angka kedua > 12 file memakai m/d/yyyy sehingga urutannya
    // dibalik. Selain itu dd/mm dipertahankan.
    const day = a > 12 ? a : b > 12 ? b : a;
    const month = a > 12 ? b : b > 12 ? a : b;

    return toIsoDate(year, month, day);
}

/** Tanggal `YYYY-MM-DD` dari sel Excel (serial, Date, atau teks). */
export function parseDateValue(value: unknown): string | null {
    const unwrapped = unwrap(value);

    if (unwrapped === null || unwrapped === undefined || unwrapped === '') {
        return null;
    }

    if (unwrapped instanceof Date) {
        return fromDate(unwrapped);
    }

    if (typeof unwrapped === 'number') {
        return fromSerial(unwrapped);
    }

    return typeof unwrapped === 'string' ? fromText(unwrapped) : null;
}

/**
 * Timestamp untuk kolom `timestamptz`. Tanggal saja diartikan pukul 00:00
 * waktu Asia/Jakarta, sama seperti data legacy yang disimpan tanpa zona.
 */
export function parseTimestampValue(value: unknown): string | null {
    const date = parseDateValue(value);

    return date ? `${date}T00:00:00+07:00` : null;
}

/** Teks sel yang sudah dirapikan; sel kosong menjadi string kosong. */
export function cellText(value: unknown): string {
    const unwrapped = unwrap(value);

    if (unwrapped === null || unwrapped === undefined) {
        return '';
    }

    if (unwrapped instanceof Date) {
        return parseDateValue(unwrapped) ?? '';
    }

    if (typeof unwrapped === 'boolean') {
        return unwrapped ? 'TRUE' : 'FALSE';
    }

    if (typeof unwrapped === 'number') {
        return Number.isFinite(unwrapped) ? String(unwrapped) : '';
    }

    return typeof unwrapped === 'string' ? unwrapped.trim() : '';
}

/** Sel kosong menjadi `null` supaya tidak menimpa data lama dengan "". */
export function cellTextOrNull(value: unknown): string | null {
    const text = cellText(value);

    return text === '' ? null : text;
}

/** Judul kolom dinormalisasi seperti `WithHeadingRow` Maatwebsite Excel. */
export function normalizeHeader(value: unknown): string {
    return cellText(value)
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '_')
        .replace(/^_+|_+$/g, '');
}
