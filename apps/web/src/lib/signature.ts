import { randomBytes } from 'node:crypto';
import { existsSync } from 'node:fs';
import { mkdir, readFile, unlink, writeFile } from 'node:fs/promises';
import path from 'node:path';
import sharp from 'sharp';
import { env } from './env';

/**
 * Penyimpanan tanda tangan karyawan.
 *
 * Berkas legacy berada di `public/ttd` dengan nilai `employees.ttd` berbentuk
 * `ttd/nama.png`. Upload baru disimpan di direktori privat (`SIGNATURE_DIR`
 * atau `apps/web/storage/ttd`) dan dibaca lewat route cetak, tetapi:
 *   - MIME diperiksa dari magic bytes, bukan `Content-Type` browser;
 *   - gambar di-decode + di-re-encode oleh sharp (metadata ikut dibuang);
 *   - nama berkas acak, jadi nama asli pengguna tidak pernah tersimpan;
 *   - batas ukuran 2 MB dan dimensi dibatasi untuk mencegah decompression bomb.
 */

export const MAX_SIGNATURE_BYTES = 2 * 1024 * 1024;

export class SignatureError extends Error {
    constructor(message: string) {
        super(message);
        this.name = 'SignatureError';
    }
}

/** Direktori berkas legacy (`public/ttd`), hanya dibaca. */
export function signatureDirectory(): string {
    const defaultDirectory = path.resolve(process.cwd(), '../../public/ttd'); // cwd = apps/web
    const candidates = [
        defaultDirectory,
        path.resolve(process.cwd(), 'public/ttd'), // cwd = root repo
        path.resolve(process.cwd(), 'apps/web/public/ttd'), // cwd = root repo
    ];

    return candidates.find((candidate) => existsSync(candidate)) ?? defaultDirectory;
}

/**
 * Upload baru tidak masuk `public/` supaya tidak bisa diakses langsung tanpa
 * sesi. Cetakan membacanya lewat route `/app/print/signature/[file]`.
 */
function uploadDirectory(): string {
    return env.SIGNATURE_DIR ?? path.resolve(process.cwd(), 'storage/ttd');
}

/** Hanya nama berkas yang dikenal; menolak path traversal. */
const FILE_PATTERN = /^[A-Za-z0-9][A-Za-z0-9._-]*$/;

export function signatureBasename(value?: string | null): string | null {
    const file = value?.split('/').filter(Boolean).pop() ?? '';

    return FILE_PATTERN.test(file) && !file.includes('..') ? file : null;
}

function isPng(bytes: Buffer): boolean {
    return (
        bytes.length > 8 &&
        bytes[0] === 0x89 &&
        bytes[1] === 0x50 &&
        bytes[2] === 0x4e &&
        bytes[3] === 0x47 &&
        bytes[4] === 0x0d &&
        bytes[5] === 0x0a &&
        bytes[6] === 0x1a &&
        bytes[7] === 0x0a
    );
}

function isJpeg(bytes: Buffer): boolean {
    return bytes.length > 3 && bytes[0] === 0xff && bytes[1] === 0xd8 && bytes[2] === 0xff;
}

/**
 * Simpan tanda tangan baru. Mengembalikan nilai untuk kolom `employees.ttd`
 * (`ttd/<nama-acak>.png`), atau `null` bila tidak ada berkas yang dikirim.
 */
export async function saveSignature(file: FormDataEntryValue | null): Promise<string | null> {
    if (!(file instanceof File) || file.size === 0) {
        return null;
    }

    if (!['image/png', 'image/jpeg'].includes(file.type)) {
        throw new SignatureError('Tanda tangan harus berupa gambar PNG atau JPG.');
    }

    if (file.size > MAX_SIGNATURE_BYTES) {
        throw new SignatureError('Ukuran tanda tangan maksimal 2 MB.');
    }

    const original = Buffer.from(await file.arrayBuffer());

    if (!isPng(original) && !isJpeg(original)) {
        throw new SignatureError('Isi berkas bukan gambar PNG/JPG yang valid.');
    }

    let normalized: Buffer;

    try {
        normalized = await sharp(original, { limitInputPixels: 40_000_000 })
            .rotate()
            .resize({ width: 900, height: 400, fit: 'inside', withoutEnlargement: true })
            .png({ compressionLevel: 9 })
            .toBuffer();
    } catch {
        throw new SignatureError('Gambar tanda tangan tidak dapat dibaca.');
    }

    const directory = uploadDirectory();
    const fileName = `ttd-${Date.now().toString(36)}-${randomBytes(6).toString('hex')}.png`;

    await mkdir(directory, { recursive: true });
    await writeFile(path.join(directory, fileName), normalized, { mode: 0o600 });

    return `ttd/${fileName}`;
}

/** Hapus berkas hasil upload sendiri; berkas legacy tidak pernah dihapus. */
export async function removeUploadedSignature(value?: string | null): Promise<void> {
    const file = signatureBasename(value);

    if (!file?.startsWith('ttd-')) return;

    try {
        await unlink(path.join(uploadDirectory(), file));
    } catch (error) {
        // Berkas bisa saja sudah dihapus manual; jangan gagalkan penyimpanan.
        console.error('Gagal menghapus tanda tangan lama.', error);
    }
}

/** Dipakai halaman cetak: baca berkas yang sudah divalidasi namanya. */
export async function readSignature(file: string): Promise<Buffer | null> {
    const safe = signatureBasename(file);

    if (!safe) return null;

    for (const directory of [uploadDirectory(), signatureDirectory()]) {
        try {
            return await readFile(path.join(directory, safe));
        } catch {
            // Coba direktori berikutnya (upload baru, lalu berkas legacy).
        }
    }

    return null;
}
