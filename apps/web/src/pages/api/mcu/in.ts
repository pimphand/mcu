import type { APIRoute } from 'astro';
import { z } from 'zod';
import {
    readSessionToken,
    setSessionCookie,
    signSession,
    verifySession,
} from '../../../lib/auth/session';
import { queryAuth } from '../../../lib/db';
import { problem } from '../../../lib/http';

export const prerender = false;

const ContextSchema = z.object({
    client_id: z.coerce.number().int().positive(),
    contract_id: z.coerce.number().int().positive(),
});

type ContractRow = {
    client_id: number;
    contract_id: number;
};

export const POST: APIRoute = async ({ request, cookies }) => {
    const token = readSessionToken(cookies);

    if (!token) {
        return problem('unauthorized', 'Sesi tidak ditemukan.');
    }

    const session = await verifySession(token);

    if (!session) {
        return problem('unauthorized', 'Sesi tidak valid atau sudah kedaluwarsa.');
    }

    if (!['admin', 'client_admin', 'employee'].includes(session.app_role)) {
        return problem('forbidden', 'Role ini tidak dapat memilih konteks MCU.');
    }

    let body: unknown;

    try {
        body = await request.json();
    } catch {
        return problem('bad_request', 'Body harus berupa JSON yang valid.');
    }

    const parsed = ContextSchema.safeParse(body);

    if (!parsed.success) {
        return problem('bad_request', 'client_id dan contract_id wajib berupa angka positif.');
    }

    const { client_id: clientId, contract_id: contractId } = parsed.data;

    // Akun staf tanpa client (`users.client_id` NULL) adalah akun klinik yang
    // boleh memilih konteks client mana pun, sama seperti MCU IN/OUT legacy.
    // Akun yang sudah ditautkan tetap terbatas ke client-nya.
    if (
        session.app_role !== 'admin' &&
        session.client_id !== null &&
        session.client_id !== clientId
    ) {
        return problem('forbidden', 'Client tidak sesuai dengan sesi Anda.');
    }

    try {
        const contract = await queryAuth<ContractRow>(
            `select c.id as client_id, ct.id as contract_id
         from public.clients c
         join public.contracts ct on ct.client_id = c.id
        where c.id = $1
          and ct.id = $2
          and ct.deleted_at is null
        limit 1`,
            [clientId, contractId],
        );

        if (!contract) {
            return problem('not_found', 'Client atau kontrak tidak ditemukan.');
        }

        const newToken = await signSession({
            app_role: session.app_role,
            user_id: session.user_id,
            role_id: session.role_id,
            level: session.level,
            client_id: contract.client_id,
            contract_id: contract.contract_id,
            token_version: session.token_version,
        });

        setSessionCookie(cookies, newToken);

        return Response.json({
            context: {
                client_id: contract.client_id,
                contract_id: contract.contract_id,
            },
        });
    } catch (error) {
        console.error('Gagal memilih konteks MCU.', error);
        return problem('bad_gateway', 'Layanan data sedang tidak tersedia.');
    }
};
