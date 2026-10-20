const ROLE_LABELS: Record<string, string> = {
    admin: 'Administrator',
    client_admin: 'Admin Client',
    employee: 'Admin Karyawan',
    participant: 'Peserta',
};

export function roleLabel(role?: string | null): string {
    if (!role) return 'Pengguna';

    return ROLE_LABELS[role] ?? role;
}

export function genderLabel(value?: string | null): string {
    if (value === 'L') return 'Laki-laki';
    if (value === 'P') return 'Perempuan';

    return value ?? '—';
}

/**
 * Usia dalam tahun penuh. Tanggal lahir legacy berupa `YYYY-MM-DD` tanpa zona
 * waktu; hari ini dihitung pada zona Asia/Jakarta agar hasil tidak bergeser
 * satu hari saat server berjalan di UTC.
 */
export function ageLabel(birthday?: string | null, now: Date = new Date()): string {
    const match = /^(\d{4})-(\d{2})-(\d{2})/.exec(birthday ?? '');

    if (!match) return '—';

    const [birthYear, birthMonth, birthDay] = [
        Number(match[1]),
        Number(match[2]),
        Number(match[3]),
    ];
    const today = jakartaParts(now);

    if (!today) return '—';

    let years = today.year - birthYear;

    if (today.month < birthMonth || (today.month === birthMonth && today.day < birthDay)) {
        years -= 1;
    }

    return years < 0 ? '—' : `${years} tahun`;
}

/**
 * Usia lengkap (tahun, bulan, hari) seperti `diff()->format('%y tahun %m bulan %d hari')`
 * pada cetakan legacy, dihitung pada zona Asia/Jakarta.
 */
export function ageDetail(birthday?: string | null, now: Date = new Date()): string {
    const match = /^(\d{4})-(\d{2})-(\d{2})/.exec(birthday ?? '');
    const today = jakartaParts(now);

    if (!match || !today) return '—';

    const birthYear = Number(match[1]);
    const birthMonth = Number(match[2]);
    const birthDay = Number(match[3]);
    const previousMonthDays = new Date(Date.UTC(today.year, today.month - 1, 0)).getUTCDate();

    let years = today.year - birthYear;
    let months = today.month - birthMonth;
    let days = today.day - birthDay;

    if (days < 0) {
        months -= 1;
        days += previousMonthDays;
    }

    if (months < 0) {
        years -= 1;
        months += 12;
    }

    if (years < 0) return '—';

    return `${years} tahun ${months} bulan ${days} hari`;
}

function jakartaParts(now: Date): { year: number; month: number; day: number } | null {
    const parts = new Intl.DateTimeFormat('en-CA', {
        timeZone: 'Asia/Jakarta',
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
    }).formatToParts(now);

    const value = (type: string) => Number(parts.find((part) => part.type === type)?.value);
    const year = value('year');
    const month = value('month');
    const day = value('day');

    if (!year || !month || !day) return null;

    return { year, month, day };
}

export function formatDateTime(value?: string | Date | null): string {
    if (!value) return '—';

    const date = value instanceof Date ? value : new Date(value);

    if (Number.isNaN(date.getTime())) return '—';

    return new Intl.DateTimeFormat('id-ID', {
        dateStyle: 'medium',
        timeStyle: 'short',
        timeZone: 'Asia/Jakarta',
    }).format(date);
}

export function formatDate(value?: string | Date | null): string {
    if (!value) return '—';

    const date = value instanceof Date ? value : new Date(value);

    if (Number.isNaN(date.getTime())) return '—';

    return new Intl.DateTimeFormat('id-ID', {
        dateStyle: 'medium',
        timeZone: 'Asia/Jakarta',
    }).format(date);
}
