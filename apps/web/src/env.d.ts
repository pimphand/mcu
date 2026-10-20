/// <reference types="astro/client" />

import type { SessionClaims } from './lib/auth/session';
import type { MenuRow } from './lib/nav';

declare global {
    namespace App {
        interface Locals {
            user?: SessionClaims;
            /** Menu + permission role user, dimuat middleware untuk halaman /app. */
            menus?: MenuRow[];
            /** Pesan bila `v_my_menus` gagal dimuat. */
            menusError?: string;
        }
    }
}

export {};
