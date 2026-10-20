import { describe, expect, it } from 'vitest';
import { canInputResult, examResults } from './exams';
import { resultFormValues, resultPayload } from './result-form';

const audiometri = examResults.find((item) => item.key === 'audiometris')!;
const laboratorium = examResults.find((item) => item.key === 'laboratoria')!;
const ekg = examResults.find((item) => item.key === 'ekgs')!;

describe('formulir hasil pemeriksaan', () => {
    it('hanya tabel hasil per peserta yang bisa diisi manual', () => {
        expect(canInputResult(audiometri)).toBe(true);
        expect(canInputResult(laboratorium)).toBe(true);

        // Hasil Umum & Validasi Dokter berasal dari upload (tanpa `participant_id`).
        for (const key of ['general_results', 'validate_doctors']) {
            const config = examResults.find((item) => item.key === key)!;
            expect(canInputResult(config)).toBe(false);
        }
    });

    it('mengisi nilai awal dari baris hasil yang sudah ada', () => {
        const values = resultFormValues(audiometri, {
            audiometri_telinga_kanan: '6/6',
            pendengaran_telinga_kanan: 'Normal',
        });

        expect(values.audiometri_telinga_kanan).toBe('6/6');
        expect(values.pendengaran_telinga_kanan).toBe('Normal');
        expect(values.kesimpulan).toBe('');
        expect(resultFormValues(audiometri, null).saran).toBe('');
    });

    it('mengubah nilai formulir menjadi payload upsert', () => {
        expect(
            resultPayload(
                audiometri,
                {
                    audiometri_telinga_kanan: ' 6/6 ',
                    audiometri_telinga_kiri: '   ',
                    kesimpulan: 'Normal',
                },
                { participantId: 388, employeeId: 2, selesai: true },
            ),
        ).toEqual({
            participant_id: 388,
            employee_id: 2,
            selesai: true,
            audiometri_telinga_kanan: '6/6',
            audiometri_telinga_kiri: null,
            pendengaran_telinga_kanan: null,
            pendengaran_telinga_kiri: null,
            kesimpulan: 'Normal',
            saran: null,
        });
    });

    it('mengonversi kolom boolean menjadi boolean atau null', () => {
        const payload = resultPayload(
            ekg,
            { takikardi: 'false', bradikardi: 'true', aritmia: '' },
            { participantId: 388, selesai: false },
        );

        expect(payload.takikardi).toBe(false);
        expect(payload.bradikardi).toBe(true);
        expect(payload.aritmia).toBeNull();
        expect(payload.selesai).toBe(false);
        expect(payload.employee_id).toBeNull();
    });

    it('mengirim datetime-local sebagai ISO dan tanggal apa adanya', () => {
        const payload = resultPayload(
            laboratorium,
            { lab_date: '2026-10-12T09:43' },
            { participantId: 388, selesai: true },
        );

        expect(payload.lab_date).toBe(new Date('2026-10-12T09:43').toISOString());

        const radiologi = examResults.find((item) => item.key === 'radiologis')!;
        expect(
            resultPayload(
                radiologi,
                { date_mcu: '2026-10-12T00:00:00.000Z' },
                { participantId: 388, selesai: true },
            ).date_mcu,
        ).toBe('2026-10-12');
    });

    it('menyertakan client dan kontrak peserta untuk laboratorium', () => {
        const payload = resultPayload(
            laboratorium,
            { hemoglobin: '15.7' },
            { participantId: 388, clientId: 1, contractId: 4, selesai: true },
        );

        expect(payload.client_id).toBe(1);
        expect(payload.contract_id).toBe(4);
        expect(payload.hemoglobin).toBe('15.7');

        // Tabel hasil lain tidak punya kolom client_id.
        const audiometriPayload = resultPayload(
            audiometri,
            {},
            { participantId: 388, clientId: 1, contractId: 4, selesai: true },
        );

        expect(audiometriPayload).not.toHaveProperty('client_id');
        expect(audiometriPayload).not.toHaveProperty('contract_id');
    });

    it('bolak-balik nilai datetime menunjuk menit yang sama', () => {
        const stored = '2026-10-12T02:43:53+00:00';
        const values = resultFormValues(laboratorium, { lab_date: stored });

        // Input `datetime-local` berpresisi menit, jadi detik terpotong.
        expect(new Date(values.lab_date ?? '').getTime()).toBe(
            Math.floor(new Date(stored).getTime() / 60_000) * 60_000,
        );
    });
});
