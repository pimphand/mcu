/** Bagi daftar menjadi potongan berukuran tetap untuk permintaan batch. */
export function chunkArray<T>(items: T[], size: number): T[][] {
    if (size < 1) {
        throw new Error('Ukuran potongan harus minimal 1.');
    }

    const chunks: T[][] = [];

    for (let index = 0; index < items.length; index += size) {
        chunks.push(items.slice(index, index + size));
    }

    return chunks;
}
