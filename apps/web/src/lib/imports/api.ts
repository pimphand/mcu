import { z } from 'zod';

export const importResponseSchema = z.object({
    status: z.literal('success'),
    job_id: z.number().nullable(),
    rows: z.number(),
    processed: z.number(),
    skipped: z.number(),
    skipped_codes: z.array(z.string()),
    created_employees: z.array(z.string()),
});

export type ImportResponse = z.infer<typeof importResponseSchema>;

const errorResponseSchema = z.object({
    message: z.string().optional(),
});

export function errorMessage(payload: unknown, fallback: string): string {
    const parsed = errorResponseSchema.safeParse(payload);

    return parsed.success && parsed.data.message ? parsed.data.message : fallback;
}
