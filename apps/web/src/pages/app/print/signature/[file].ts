import type { APIRoute } from 'astro';
import { existsSync } from 'node:fs';
import { readFile } from 'node:fs/promises';
import path from 'node:path';
import { readSignature, signatureBasename, signatureDirectory } from '@/lib/signature';

export const prerender = false;

const CONTENT_TYPES: Record<string, string> = {
    png: 'image/png',
    jpg: 'image/jpeg',
    jpeg: 'image/jpeg',
};

/** Gambar tanda tangan kosong legacy, dipakai bila berkas tidak ditemukan. */
function fallbackPath(): string | null {
    const candidates = [
        path.resolve(signatureDirectory(), '..', 'images', 'ttd-kosong.png'),
        path.resolve(process.cwd(), '../../public/images/ttd-kosong.png'),
        path.resolve(process.cwd(), 'public/ttd-kosong.png'),
        path.resolve(process.cwd(), 'apps/web/public/ttd-kosong.png'),
    ];

    return candidates.find((candidate) => existsSync(candidate)) ?? null;
}

async function imageResponse(bytes: Buffer, contentType: string): Promise<Response> {
    return new Response(new Uint8Array(bytes), {
        headers: {
            'content-type': contentType,
            'cache-control': 'private, max-age=300',
            'x-content-type-options': 'nosniff',
        },
    });
}

export const GET: APIRoute = async ({ params }) => {
    const file = signatureBasename(params.file ?? '');

    if (!file) {
        return new Response('Berkas tidak valid.', { status: 404 });
    }

    const contentType = CONTENT_TYPES[path.extname(file).slice(1).toLowerCase()];

    if (!contentType) {
        return new Response('Berkas tidak valid.', { status: 404 });
    }

    const signature = await readSignature(file);

    if (signature) {
        return imageResponse(signature, contentType);
    }

    // Legacy menampilkan `images/ttd-kosong.png` bila karyawan belum punya ttd.
    const fallback = fallbackPath();

    if (fallback) {
        try {
            return await imageResponse(await readFile(fallback), 'image/png');
        } catch {
            // Lanjut ke 404 di bawah bila berkas fallback tidak terbaca.
        }
    }

    return new Response('Tanda tangan tidak ditemukan.', { status: 404 });
};
