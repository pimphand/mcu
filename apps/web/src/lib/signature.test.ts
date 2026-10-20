import { mkdtemp, rm } from 'node:fs/promises';
import os from 'node:os';
import path from 'node:path';
import sharp from 'sharp';
import { afterAll, beforeAll, describe, expect, it, vi } from 'vitest';

let directory = '';

beforeAll(async () => {
    directory = await mkdtemp(path.join(os.tmpdir(), 'mcu-ttd-'));
    vi.stubEnv('SIGNATURE_DIR', directory);
});

afterAll(async () => {
    vi.unstubAllEnvs();
    await rm(directory, { recursive: true, force: true });
});

async function signatureModule() {
    return import('./signature');
}

describe('upload tanda tangan', () => {
    it('menyimpan ulang PNG dengan nama acak dan bisa dibaca kembali', async () => {
        const { readSignature, removeUploadedSignature, saveSignature } = await signatureModule();
        const png = await sharp({
            create: { width: 8, height: 8, channels: 3, background: '#ffffff' },
        })
            .png()
            .toBuffer();

        const result = await saveSignature(new File([png], 'nama asli.png', { type: 'image/png' }));

        expect(result).toMatch(/^ttd\/ttd-[a-z0-9]+-[a-f0-9]+\.png$/);
        expect(result).not.toContain('nama asli');

        const file = result?.split('/').pop() ?? '';
        const stored = await readSignature(file);

        expect(stored?.subarray(0, 4)).toEqual(Buffer.from([0x89, 0x50, 0x4e, 0x47]));

        await removeUploadedSignature(result);
        expect(await readSignature(file)).toBeNull();
    });

    it('menolak berkas yang bukan gambar walau MIME-nya diklaim PNG', async () => {
        const { saveSignature } = await signatureModule();
        const file = new File([Buffer.from('bukan gambar')], 'palsu.png', { type: 'image/png' });

        await expect(saveSignature(file)).rejects.toThrow('bukan gambar PNG/JPG');
    });

    it('menolak berkas lebih dari 2 MB sebelum decode', async () => {
        const { saveSignature } = await signatureModule();
        const file = new File([new Uint8Array(2 * 1024 * 1024 + 1)], 'besar.png', {
            type: 'image/png',
        });

        await expect(saveSignature(file)).rejects.toThrow('maksimal 2 MB');
    });

    it('mengabaikan form tanpa berkas', async () => {
        const { saveSignature } = await signatureModule();

        await expect(saveSignature(null)).resolves.toBeNull();
        await expect(saveSignature('')).resolves.toBeNull();
    });
});
