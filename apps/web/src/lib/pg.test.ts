import { describe, expect, it } from 'vitest';
import { normalizePgConnection, sslOptionsForMode } from './pg';

describe('sslOptionsForMode', () => {
    it('memetakan mode libpq tanpa verifikasi ke rejectUnauthorized: false', () => {
        expect(sslOptionsForMode('require')).toEqual({ rejectUnauthorized: false });
        expect(sslOptionsForMode('prefer')).toEqual({ rejectUnauthorized: false });
    });

    it('mematikan TLS untuk disable/allow dan tidak diatur bila kosong', () => {
        expect(sslOptionsForMode('disable')).toBe(false);
        expect(sslOptionsForMode('allow')).toBe(false);
        expect(sslOptionsForMode(undefined)).toBeUndefined();
        expect(sslOptionsForMode('')).toBeUndefined();
    });

    it('memverifikasi sertifikat untuk verify-ca/verify-full', () => {
        expect(sslOptionsForMode('verify-full')).toEqual({ rejectUnauthorized: true });
        expect(sslOptionsForMode('verify-ca')).toEqual({ rejectUnauthorized: true });
    });

    it('menolak mode yang tidak dikenal', () => {
        expect(() => sslOptionsForMode('kadang')).toThrow(/sslmode tidak dikenal/);
    });
});

describe('normalizePgConnection', () => {
    it('mengeluarkan sslmode dari URL agar pg tidak memaksa verify-full', () => {
        const { connectionString, ssl } = normalizePgConnection(
            'postgresql://postgres:secret@db.example.supabase.co:5432/postgres?sslmode=require',
        );

        expect(connectionString).toBe(
            'postgresql://postgres:secret@db.example.supabase.co:5432/postgres',
        );
        expect(ssl).toEqual({ rejectUnauthorized: false });
    });

    it('membiarkan parameter lain dan menganggap tanpa sslmode sebagai tanpa TLS', () => {
        const { connectionString, ssl } = normalizePgConnection(
            'postgresql://mcu_auth:pw@127.0.0.1:5432/mcu?application_name=mcu-web',
        );

        expect(connectionString).toContain('application_name=mcu-web');
        expect(connectionString).not.toContain('sslmode');
        expect(ssl).toBeUndefined();
    });
});
