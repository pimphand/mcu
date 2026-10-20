import { z } from 'zod';
import { masterConfig, type MasterField, type MasterKind } from './master-data';

/**
 * Validasi form master data. Aturan diturunkan dari `masterConfigs` supaya
 * field yang tampil di form dan yang diterima endpoint tidak pernah berbeda.
 */

export type MasterValues = Record<string, string | number | boolean | null>;
export type MasterMode = 'create' | 'update';

export type MasterValidation = { ok: true; values: MasterValues } | { ok: false; message: string };

const DATE_PATTERN = /^\d{4}-\d{2}-\d{2}$/;

function text(value: FormDataEntryValue | null): string {
    return typeof value === 'string' ? value.trim() : '';
}

function fieldValue(field: MasterField, form: FormData, mode: MasterMode): unknown {
    if (field.type === 'boolean') {
        return form.get(field.key) === 'true';
    }

    if (field.type === 'file') {
        return undefined;
    }

    const raw = text(form.get(field.key));

    if (field.type === 'number') {
        if (!raw && !field.required) return null;

        return raw === '' ? Number.NaN : Number(raw);
    }

    if (field.type === 'select') {
        // Select yang bersumber dari tabel berisi id (client/role/menu induk)
        // dikirim sebagai angka, bukan string, agar tipe kolom PostgreSQL jelas.
        if (!raw) return field.required ? '' : null;

        return field.optionSource ? Number(raw) : raw;
    }

    if (field.type === 'password' && mode === 'update' && !raw) {
        return undefined;
    }

    return raw;
}

function fieldSchema(field: MasterField, mode: MasterMode): z.ZodType {
    const required = field.required || (field.requiredOnCreate && mode === 'create');

    if (field.type === 'boolean') {
        return z.boolean();
    }

    if (field.type === 'number') {
        let schema = z.number({ error: `${field.label} harus berupa angka.` }).int();

        if (field.min !== undefined) {
            schema = schema.min(field.min, `${field.label} minimal ${field.min}.`);
        }

        if (field.max !== undefined) {
            schema = schema.max(field.max, `${field.label} maksimal ${field.max}.`);
        }

        return required ? schema : schema.nullable();
    }

    if (field.type === 'select') {
        if (field.optionSource) {
            const idSchema = z
                .number({ error: `${field.label} wajib dipilih.` })
                .int()
                .positive(`${field.label} wajib dipilih.`);

            return required ? idSchema : idSchema.nullable();
        }

        return required
            ? z.string().min(1, `${field.label} wajib dipilih.`)
            : z.string().nullable();
    }

    if (field.type === 'email') {
        return required
            ? z.email(`${field.label} tidak valid.`).max(field.maxLength ?? 255)
            : z
                  .string()
                  .max(field.maxLength ?? 255)
                  .refine((value) => value === '' || z.email().safeParse(value).success, {
                      message: `${field.label} tidak valid.`,
                  })
                  .nullable();
    }

    if (field.type === 'date') {
        return z
            .string()
            .refine((value) => value === '' || DATE_PATTERN.test(value), {
                message: `${field.label} harus berformat YYYY-MM-DD.`,
            })
            .nullable();
    }

    if (field.type === 'password') {
        const min = z
            .string()
            .min(8, `${field.label} minimal 8 karakter.`)
            .max(field.maxLength ?? 255);

        return required ? min : min.optional();
    }

    if (field.type === 'file') {
        return z.undefined();
    }

    const base = z.string().max(field.maxLength ?? 255, `${field.label} terlalu panjang.`);

    if (required) {
        return base.min(1, `${field.label} wajib diisi.`);
    }

    return base;
}

export function validateMasterForm(
    kind: MasterKind,
    form: FormData,
    mode: MasterMode,
): MasterValidation {
    const config = masterConfig(kind);
    const values: MasterValues = {};

    for (const field of config.fields) {
        const value = fieldValue(field, form, mode);
        const parsed = fieldSchema(field, mode).safeParse(value);

        if (!parsed.success) {
            const issue = parsed.error.issues[0];

            return {
                ok: false,
                message: issue?.message ?? `${field.label} tidak valid.`,
            };
        }

        if (parsed.data !== undefined) {
            values[field.key] = parsed.data as string | number | boolean | null;
        }
    }

    // Tanggal kosong disimpan sebagai NULL, bukan string kosong yang ditolak
    // kolom `date` PostgreSQL.
    for (const field of config.fields) {
        if (field.type === 'date' && values[field.key] === '') {
            values[field.key] = null;
        }
    }

    return { ok: true, values };
}

/** Kolom audit yang diisi server, bukan dari form. */
export function auditColumns(sessionUserId: number, mode: MasterMode): Record<string, number> {
    return mode === 'create'
        ? { created_by: sessionUserId, updated_by: sessionUserId }
        : { updated_by: sessionUserId };
}

export function parseId(value: FormDataEntryValue | null): number | null {
    const parsed = Number(text(value));

    return Number.isInteger(parsed) && parsed > 0 ? parsed : null;
}

export function optionalId(value: FormDataEntryValue | null): number | null {
    const raw = text(value);

    if (!raw) return null;

    return parseId(raw);
}

export function numberValue(value: MasterValues[string]): number | null {
    return typeof value === 'number' && Number.isFinite(value) ? value : null;
}
