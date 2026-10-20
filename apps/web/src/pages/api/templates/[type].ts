import type { APIRoute } from 'astro';
import { problem } from '../../../lib/http';
import { isImportType } from '../../../lib/imports/types';
import { handleTemplateRequest } from '../../../server/imports/template-handler';

export const prerender = false;

export const GET: APIRoute = async (context) => {
    const type = context.params.type;

    if (!isImportType(type)) {
        return problem('not_found', 'Jenis template tidak dikenal.');
    }

    return handleTemplateRequest(type, context);
};
