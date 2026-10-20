import {
    IDENTITAS_SELECT,
    identitasFilter,
    mapIdentitas,
    type IdentitasParticipant,
    type IdentitasQuery,
    type IdentitasRow,
} from './identitas';
import { postgrestRows } from './server-postgrest';

/**
 * Data stiker dibaca langsung dari `participants` (bukan `v_mcu_progress`)
 * karena view legacy memakai `plan_name`, yang tidak ada di view progres.
 * RLS `participants_read` tetap membatasi client/kontrak.
 */
export async function fetchIdentitasParticipants(
    token: string,
    clientId: number,
    contractId: number,
    query: IdentitasQuery,
): Promise<IdentitasParticipant[]> {
    const rows = await postgrestRows<IdentitasRow>(
        token,
        'participants',
        {
            select: IDENTITAS_SELECT,
            client_id: `eq.${clientId}`,
            contract_id: `eq.${contractId}`,
            and: identitasFilter(query),
            order: 'no_form.asc',
            limit: 2000,
        },
        'public',
    );

    return rows.map(mapIdentitas);
}
