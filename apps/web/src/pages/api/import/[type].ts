import type { APIRoute } from 'astro';
import { problem } from '../../../lib/http';
import { isImportType } from '../../../lib/imports/types';
import { handleImportRequest } from '../../../server/imports/handler';

export const prerender = false;

export const POST: APIRoute = async (context) => {
    const type = context.params.type;

    if (!isImportType(type)) {
        return problem('not_found', 'Jenis import tidak dikenal.');
    }

    return handleImportRequest(type, context);
};
