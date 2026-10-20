import type { APIContext } from 'astro';
import { buildImportTemplate } from '../../lib/imports/template';
import type { ImportType } from '../../lib/imports/types';
import { templateFileNames } from '../../lib/upload-reports';
import { authorizeImport } from './authorize';

export async function handleTemplateRequest(
    type: ImportType,
    context: APIContext,
): Promise<Response> {
    const auth = await authorizeImport(context.cookies, type, 'view');

    if (auth instanceof Response) {
        return auth;
    }

    try {
        const buffer = await buildImportTemplate(type);
        const fileName = templateFileNames[type];

        return new Response(new Uint8Array(buffer), {
            status: 200,
            headers: {
                'content-type': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'content-disposition': `attachment; filename="${fileName}"`,
                'content-length': String(buffer.byteLength),
                'cache-control': 'no-store',
            },
        });
    } catch (error) {
        console.error(`Gagal membuat template ${type}.`, error);

        return new Response(
            JSON.stringify({
                error: 'internal',
                message: 'Template tidak dapat dibuat.',
            }),
            {
                status: 500,
                headers: { 'content-type': 'application/json; charset=utf-8' },
            },
        );
    }
}
