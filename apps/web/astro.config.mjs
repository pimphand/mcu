import { fileURLToPath } from 'node:url';
import { defineConfig } from 'astro/config';
import node from '@astrojs/node';
import vue from '@astrojs/vue';
import tailwindcss from '@tailwindcss/vite';

const site = process.env.PUBLIC_APP_URL ?? 'http://localhost:4321';
const siteUrl = new URL(site);
const isHttps = siteUrl.protocol === 'https:';

export default defineConfig({
    site,
    output: 'server',
    adapter: node({ mode: 'standalone' }),
    integrations: [vue()],
    // Aplikasi ini tidak merender blok kode Markdown, sedangkan penyorot sintaks
    // Shiki menulis style inline yang tidak kompatibel dengan CSP. Dimatikan
    // supaya peringatan Astro hilang tanpa melepas CSP (invariant keamanan 8).
    markdown: {
        syntaxHighlight: false,
    },
    vite: {
        plugins: [tailwindcss()],
        resolve: {
            alias: {
                '@': fileURLToPath(new URL('./src', import.meta.url)),
            },
        },
    },
    security: {
        checkOrigin: true,
        allowedDomains: isHttps ? [{ hostname: siteUrl.hostname, protocol: 'https' }] : [],
        csp: {
            algorithm: 'SHA-256',
            directives: [
                "default-src 'self'",
                "img-src 'self' data: blob:",
                "connect-src 'self'",
                "font-src 'self'",
                "base-uri 'self'",
                "form-action 'self'",
            ],
        },
    },
});
