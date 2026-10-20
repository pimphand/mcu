import eslint from '@eslint/js';
import astro from 'eslint-plugin-astro';
import globals from 'globals';
import tseslint from 'typescript-eslint';

export default tseslint.config(
    {
        ignores: ['dist/**', '.astro/**', 'node_modules/**'],
    },
    eslint.configs.recommended,
    ...tseslint.configs.recommended,
    ...astro.configs.recommended,
    {
        rules: {
            '@typescript-eslint/no-explicit-any': 'error',
            '@typescript-eslint/consistent-type-imports': 'error',
            'no-console': ['warn', { allow: ['warn', 'error'] }],
        },
    },
    {
        files: ['*.config.{js,mjs,ts}', 'vitest.config.ts'],
        languageOptions: {
            globals: globals.node,
        },
    },
);
