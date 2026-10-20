/** Jenis import hasil yang didukung (sama dengan check constraint `import_jobs.type`). */
export type ImportType = 'validate_doctor' | 'laboratorium' | 'radiologi' | 'general_result';

export const IMPORT_TYPES: readonly ImportType[] = [
    'validate_doctor',
    'laboratorium',
    'radiologi',
    'general_result',
];

export function isImportType(value: string | undefined): value is ImportType {
    return IMPORT_TYPES.includes(value as ImportType);
}

/** Satu kolom yang dibaca dari file dan namanya di tabel PostgreSQL. */
export type FieldSpec = {
    column: string;
    heading: string;
    /** Judul alternatif dari file lama (sudah dinormalisasi). */
    aliases?: string[];
};

export type ImportContext = {
    token: string;
    userId: number;
    clientId: number;
    contractId: number;
};

export type ImportOutcome = {
    processed: number;
    skipped: number;
    skippedCodes: string[];
    createdEmployees: string[];
};

export type ImportParticipant = {
    id: number;
    code: string;
    name: string;
    gender: string;
    no_form: number | null;
    register_date: string | null;
    client_id: number;
    contract_id: number;
};
