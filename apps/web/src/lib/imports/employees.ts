import { postgrestRows, postgrestRpc, postgrestSend } from '../server-postgrest';
import type { ImportContext } from './types';

export type EmployeeRow = {
    id: number;
    nama: string | null;
    client_id: number | null;
};

export function normalizeName(value: string | null | undefined): string | null {
    const normalized = (value ?? '').replace(/\s+/g, ' ').trim();

    return normalized === '' ? null : normalized;
}

/**
 * Buang gelar setelah koma saja ("Murni Islamiati, A.Md.A.K" -> "Murni
 * Islamiati"). Gelar depan seperti "dr." adalah bagian nama.
 */
export function withoutTitle(name: string): string {
    return name.replace(/\s*,.*$/su, '').trim();
}

function matches(candidate: EmployeeRow, base: string, full: string): boolean {
    const stored = (candidate.nama ?? '').replace(/\s+/g, ' ').trim().toLowerCase();
    const baseLower = base.toLowerCase();
    const fullLower = full.toLowerCase();

    return stored === baseLower || stored.startsWith(`${baseLower},`) || stored === fullLower;
}

function rank(candidate: EmployeeRow, full: string, clientId: number): number[] {
    const stored = (candidate.nama ?? '').trim().toLowerCase();

    return [
        stored.includes(',') ? 0 : 1,
        stored === full.toLowerCase() ? 0 : 1,
        candidate.client_id === clientId ? 0 : 1,
        candidate.id,
    ];
}

/**
 * Pilih karyawan terbaik untuk satu nama file. Urutan mengikuti
 * `EmployeeService::findByName`: yang punya gelar setelah koma, nama persis,
 * client aktif, lalu id terkecil.
 */
export function pickEmployee(
    candidates: EmployeeRow[],
    name: string,
    clientId: number,
): EmployeeRow | null {
    const normalized = normalizeName(name);

    if (!normalized) {
        return null;
    }

    const base = withoutTitle(normalized) || normalized;
    const matchesBase = candidates.filter((row) => matches(row, base, normalized));

    matchesBase.sort((a, b) => {
        const left = rank(a, normalized, clientId);
        const right = rank(b, normalized, clientId);

        for (let index = 0; index < left.length; index += 1) {
            const diff = (left[index] ?? 0) - (right[index] ?? 0);

            if (diff !== 0) return diff;
        }

        return 0;
    });

    return matchesBase[0] ?? null;
}

/**
 * Resolve pemeriksa dari file ke `employees`; bila belum ada, dibuat baru
 * (bukan ditempelkan ke dokter lain). Urutan kandidat mengikuti
 * `EmployeeService::findByName` legacy, termasuk prioritas client aktif.
 */
export class EmployeeResolver {
    private readonly cache = new Map<string, number>();
    private readonly resolved: EmployeeRow[] = [];
    private readonly created: string[] = [];

    constructor(private readonly context: ImportContext) {}

    async idFor(name: string | null | undefined): Promise<number | null> {
        const normalized = normalizeName(name);

        if (!normalized) {
            return null;
        }

        const key = normalized.toLowerCase();
        const cached = this.cache.get(key);

        if (cached !== undefined) {
            return cached;
        }

        const candidates = await this.candidates();
        const existing = pickEmployee(candidates, normalized, this.context.clientId);

        if (existing) {
            this.cache.set(key, existing.id);
            return existing.id;
        }

        const code = await postgrestRpc<string>(this.context.token, 'next_employee_code');
        const rows = await postgrestSend<EmployeeRow>(this.context.token, 'employees', 'POST', {
            nama: normalized,
            code,
            client_id: this.context.clientId,
            created_by: this.context.userId,
        });
        const created = rows[0];

        if (!created) {
            throw new Error('Karyawan baru tidak mengembalikan id.');
        }

        this.cache.set(key, created.id);
        this.created.push(normalized);
        this.resolved.push(created);

        return created.id;
    }

    get createdEmployees(): string[] {
        return [...this.created];
    }

    private async candidates(): Promise<EmployeeRow[]> {
        if (this.resolved.length > 0) {
            return this.resolved;
        }

        const rows = await postgrestRows<EmployeeRow>(
            this.context.token,
            'employees',
            {
                select: 'id,nama,client_id',
                order: 'id.asc',
                limit: 2000,
            },
            'public',
        );

        this.resolved.push(...rows);

        return this.resolved;
    }
}
