import type { APIContext } from 'astro';
import { json, problem } from '../../lib/http';
import { rateLimit } from '../../lib/rate-limit';
import { PostgrestError } from '../../lib/server-postgrest';
import { IMPORTERS } from '../../lib/imports/importers';
import { finishImportJob, startImportJob } from '../../lib/imports/jobs';
import type { ImportContext, ImportOutcome, ImportType } from '../../lib/imports/types';
import {
    ImportFileError,
    MAX_IMPORT_BYTES,
    isLegacyXlsBuffer,
    isXlsxBuffer,
    readWorkbookRows,
    type ImportRow,
} from '../../lib/imports/workbook';
import { authorizeImport } from './authorize';

const MAX_IMPORTS_PER_MINUTE = 10;

function fieldFile(form: FormData): File | null {
    const raw = form.get('file') ?? form.get('fileExcel');

    return raw instanceof File ? raw : null;
}

function tooMany(retryAfterSeconds: number): Response {
    return json(
        {
            error: 'too_many_requests',
            message: `Terlalu banyak unggahan. Coba lagi dalam ${retryAfterSeconds} detik.`,
        },
        { status: 429, headers: { 'retry-after': String(retryAfterSeconds) } },
    );
}

export async function handleImportRequest(
    type: ImportType,
    context: APIContext,
): Promise<Response> {
    const auth = await authorizeImport(context.cookies, type, 'add');

    if (auth instanceof Response) {
        return auth;
    }

    const limit = rateLimit(`import:${auth.session.user_id}`, MAX_IMPORTS_PER_MINUTE, 60_000);

    if (!limit.ok) {
        return tooMany(limit.retryAfterSeconds);
    }

    const contentType = context.request.headers.get('content-type') ?? '';

    if (!contentType.toLowerCase().startsWith('multipart/form-data')) {
        return problem('bad_request', 'Unggahan harus berupa form multipart dengan file Excel.');
    }

    let form: FormData;

    try {
        form = await context.request.formData();
    } catch {
        return problem('bad_request', 'Form unggahan tidak dapat dibaca.');
    }

    const file = fieldFile(form);

    if (!file) {
        return problem('bad_request', 'Pilih file Excel (.xlsx/.xlsm) yang akan diunggah.');
    }

    if (file.size === 0) {
        return problem('bad_request', 'File Excel kosong.');
    }

    if (file.size > MAX_IMPORT_BYTES) {
        return problem('payload_too_large', 'Ukuran file melebihi batas 10 MB.');
    }

    const buffer = await file.arrayBuffer();

    // Penilaian berbasis **isi** file, bukan nama/ekstensi: berkas yang diunduh
    // atau di-rename dengan nama aneh (mis. `data.xlsx ` atau `laporan.xlsm`)
    // tetap bisa diunggah selama isinya workbook Excel.
    if (isLegacyXlsBuffer(buffer)) {
        return problem(
            'bad_request',
            'File .xls (Excel 97-2003) belum didukung. Buka file di Excel, simpan ulang sebagai .xlsx atau .xlsm, lalu unggah kembali.',
        );
    }

    if (!isXlsxBuffer(buffer)) {
        return problem('bad_request', 'Isi file bukan workbook Excel (.xlsx/.xlsm) yang valid.');
    }

    let rows: ImportRow[];
    let outcome: ImportOutcome;

    try {
        rows = await readWorkbookRows(buffer);
    } catch (error) {
        if (error instanceof ImportFileError) {
            return problem('bad_request', error.message);
        }

        throw error;
    }

    const session = auth.session;

    if (session.client_id === null || session.contract_id === null) {
        return problem('forbidden', 'Pilih client dan kontrak MCU terlebih dahulu.');
    }

    const importContext: ImportContext = {
        token: auth.token,
        userId: session.user_id,
        clientId: session.client_id,
        contractId: session.contract_id,
    };

    try {
        const jobId = await startImportJob(importContext, type);

        try {
            outcome = await IMPORTERS[type](importContext, rows);
        } catch (error) {
            await finishImportJob(importContext, jobId, 'failed');
            throw error;
        }

        await finishImportJob(importContext, jobId, 'completed', outcome);

        return json({
            status: 'success',
            job_id: jobId,
            rows: rows.length,
            processed: outcome.processed,
            skipped: outcome.skipped,
            skipped_codes: outcome.skippedCodes,
            created_employees: outcome.createdEmployees,
        });
    } catch (error) {
        // Pesan PostgREST tidak dikirim ke browser karena bisa memuat detail
        // baris; pengguna hanya perlu tahu import gagal dan bisa mencoba lagi.
        console.error(`Import ${type} gagal.`, error);

        if (error instanceof PostgrestError && (error.status === 401 || error.status === 403)) {
            return problem('forbidden', 'Sesi tidak berwenang menyimpan hasil import.');
        }

        return problem(
            'bad_gateway',
            'Hasil tidak dapat disimpan. Periksa isi file lalu coba lagi.',
        );
    }
}
