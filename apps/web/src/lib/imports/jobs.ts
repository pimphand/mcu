import { postgrestSend } from '../server-postgrest';
import type { ImportContext, ImportOutcome, ImportType } from './types';

/**
 * Catat awal import. Nama file tidak disimpan karena bisa memuat nama pasien;
 * yang diaudit adalah jenis, scope, dan hasil akhirnya.
 */
export async function startImportJob(
    context: ImportContext,
    type: ImportType,
): Promise<number | null> {
    const rows = await postgrestSend<{ id: number }>(context.token, 'import_jobs', 'POST', {
        user_id: context.userId,
        client_id: context.clientId,
        contract_id: context.contractId,
        type,
        status: 'processing',
    });

    return rows[0]?.id ?? null;
}

export async function finishImportJob(
    context: ImportContext,
    jobId: number | null,
    status: 'completed' | 'failed',
    outcome?: ImportOutcome,
): Promise<void> {
    if (jobId === null) {
        return;
    }

    try {
        await postgrestSend(
            context.token,
            'import_jobs',
            'PATCH',
            {
                status,
                processed: outcome?.processed ?? 0,
                skipped: outcome?.skipped ?? 0,
                errors: outcome?.skippedCodes ?? [],
            },
            { id: `eq.${jobId}` },
            'return=minimal',
        );
    } catch (error) {
        // Data hasil sudah tersimpan; kegagalan audit tidak boleh mengubah
        // respons menjadi error, tetapi tetap harus terlihat di log server.
        console.error('Gagal memperbarui status import_jobs.', error);
    }
}
