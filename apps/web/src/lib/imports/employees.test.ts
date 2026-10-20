import { describe, expect, it } from 'vitest';
import { normalizeName, pickEmployee, withoutTitle, type EmployeeRow } from './employees';

const employees: EmployeeRow[] = [
    { id: 5, nama: 'Murni Islamiati, A.Md.A.K', client_id: 2 },
    { id: 3, nama: 'Murni Islamiati', client_id: 1 },
    { id: 4, nama: 'dr. Ayu Setyowati, Sp.Rad', client_id: 2 },
    { id: 9, nama: 'dr. Ayu Setyowati, Sp.Rad', client_id: 1 },
    { id: 7, nama: 'dr. Ayu Setyowati', client_id: 1 },
    { id: 11, nama: 'Budi, A.Md', client_id: 1 },
    { id: 12, nama: 'Budi, S.Ked', client_id: 1 },
];

describe('employee name helpers', () => {
    it('collapses whitespace and removes only the degree after a comma', () => {
        expect(normalizeName('  Murni   Islamiati ')).toBe('Murni Islamiati');
        expect(normalizeName('   ')).toBeNull();
        expect(withoutTitle('Murni Islamiati, A.Md.A.K')).toBe('Murni Islamiati');
        expect(withoutTitle('dr. Ayu Setyowati, Sp.Rad')).toBe('dr. Ayu Setyowati');
        expect(withoutTitle('dr. Ayu Setyowati')).toBe('dr. Ayu Setyowati');
    });

    it('prefers the stored name that has a degree after the comma', () => {
        expect(pickEmployee(employees, 'Murni Islamiati', 1)?.id).toBe(5);
        expect(pickEmployee(employees, 'dr. Ayu Setyowati', 1)?.id).toBe(9);
    });

    it('prefers the active client when names are equally complete', () => {
        expect(pickEmployee(employees, 'dr. Ayu Setyowati, Sp.Rad', 1)?.id).toBe(9);
        expect(pickEmployee(employees, 'dr. Ayu Setyowati, Sp.Rad', 2)?.id).toBe(4);
    });

    it('prefers the exact full name before the smallest id', () => {
        expect(pickEmployee(employees, 'Budi, S.Ked', 1)?.id).toBe(12);
        expect(pickEmployee(employees, 'Budi', 1)?.id).toBe(11);
    });

    it('does not attach a result to a different person', () => {
        expect(pickEmployee(employees, 'Siti Aminah', 1)).toBeNull();
        expect(pickEmployee(employees, '', 1)).toBeNull();
    });
});
