import { describe, expect, it } from 'vitest';
import { buildImportTemplate } from './template';
import {
    GENERAL_FIELDS,
    RADIOLOGI_FIELDS,
    TEMPLATE_EXAMPLES,
    TEMPLATE_FIELDS,
    VALIDATE_FIELDS,
    fieldValue,
    isFormNumber,
    readCode,
} from './fields';
import { LAB_FIELDS } from './lab-fields';
import { IMPORT_TYPES } from './types';
import { readWorkbookRows, type ImportRow } from './workbook';

function row(values: Record<string, unknown>): ImportRow {
    return { rowNumber: 2, values };
}

describe('field definitions', () => {
    it('keeps one column per database field', () => {
        for (const fields of [LAB_FIELDS, RADIOLOGI_FIELDS, VALIDATE_FIELDS, GENERAL_FIELDS]) {
            const columns = fields.map((field) => field.column);

            expect(new Set(columns).size).toBe(columns.length);
        }

        expect(TEMPLATE_FIELDS.laboratorium).toBe(LAB_FIELDS);
    });

    it('reads legacy heading aliases', () => {
        expect(fieldValue(row({ no_mcu: 'MCU00001' }), VALIDATE_FIELDS[1]!)).toBe('MCU00001');
        expect(
            fieldValue(
                row({ nbatang: '2' }),
                LAB_FIELDS.find((f) => f.column === 'batang')!,
            ),
        ).toBe('2');
        expect(
            fieldValue(
                row({ sedimenepitel: '1' }),
                LAB_FIELDS.find((f) => f.column === 'sedimen_epitel')!,
            ),
        ).toBe('1');
        expect(
            fieldValue(
                row({ diagnosa_akhir: 'FIT' }),
                GENERAL_FIELDS.find((f) => f.column === 'result')!,
            ),
        ).toBe('FIT');
    });

    it('distinguishes a pure form number from an MCU code', () => {
        expect(isFormNumber('17')).toBe(true);
        expect(isFormNumber('MCU00017')).toBe(false);
        expect(readCode(row({ code: 17 }), LAB_FIELDS[0]!)).toBe('17');
        expect(readCode(row({ no_mcu: '  MCU00017 ' }), RADIOLOGI_FIELDS[0]!)).toBe('MCU00017');
    });
});

describe('upload templates', () => {
    it('contains the fields the importer reads for every type', async () => {
        for (const type of IMPORT_TYPES) {
            const buffer = await buildImportTemplate(type);
            const rows = await readWorkbookRows(new Uint8Array(buffer).buffer);

            expect(rows).toHaveLength(1);

            const example = TEMPLATE_EXAMPLES[type];

            for (const field of TEMPLATE_FIELDS[type]) {
                const expected = example[field.column];

                if (!expected) continue;

                expect(fieldValue(rows[0]!, field), `${type}.${field.column}`).toBe(expected);
            }
        }
    });

    it('uses unique headings so no template column is ignored', () => {
        for (const type of IMPORT_TYPES) {
            const headings = TEMPLATE_FIELDS[type].map((field) => field.heading);

            expect(new Set(headings).size, type).toBe(headings.length);
        }
    });
});
