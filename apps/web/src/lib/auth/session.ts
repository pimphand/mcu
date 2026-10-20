import type { AstroCookies } from 'astro';
import { SignJWT, jwtVerify, type JWTPayload } from 'jose';
import { cookieIsSecure, env, sessionCookieName } from '../env';

export type AppRole = 'admin' | 'client_admin' | 'employee' | 'participant';

export type SessionClaims = JWTPayload & {
    role: 'authenticated';
    app_role: AppRole;
    user_id: number;
    role_id: number;
    level: number;
    client_id: number | null;
    contract_id: number | null;
    token_version: number;
};

export type SessionInput = Omit<SessionClaims, 'role' | 'iat' | 'exp'>;

const encoder = new TextEncoder();

function jwtSecret(): Uint8Array {
    if (!env.JWT_SECRET) {
        throw new Error('JWT_SECRET belum dikonfigurasi.');
    }

    return encoder.encode(env.JWT_SECRET);
}

export function appRoleFromLevel(level: number): AppRole {
    if (level === 1) return 'admin';
    if (level === 2) return 'client_admin';
    if (level === 3) return 'employee';
    return 'participant';
}

export async function signSession(input: SessionInput): Promise<string> {
    return new SignJWT({ ...input, role: 'authenticated' satisfies SessionClaims['role'] })
        .setProtectedHeader({ alg: 'HS256', typ: 'JWT' })
        .setIssuedAt()
        .setExpirationTime(`${env.JWT_TTL_SECONDS}s`)
        .sign(jwtSecret());
}

export async function verifySession(token: string): Promise<SessionClaims | null> {
    try {
        const { payload } = await jwtVerify(token, jwtSecret(), { algorithms: ['HS256'] });

        if (
            payload.role !== 'authenticated' ||
            typeof payload.user_id !== 'number' ||
            typeof payload.role_id !== 'number' ||
            typeof payload.level !== 'number' ||
            typeof payload.app_role !== 'string' ||
            typeof payload.token_version !== 'number'
        ) {
            return null;
        }

        return payload as SessionClaims;
    } catch {
        return null;
    }
}

export function readSessionToken(cookies: AstroCookies): string | undefined {
    return cookies.get(sessionCookieName)?.value;
}

export function setSessionCookie(cookies: AstroCookies, token: string): void {
    cookies.set(sessionCookieName, token, {
        httpOnly: true,
        secure: cookieIsSecure(),
        sameSite: 'lax',
        path: '/',
        maxAge: env.JWT_TTL_SECONDS,
    });
}

export function clearSessionCookie(cookies: AstroCookies): void {
    cookies.delete(sessionCookieName, { path: '/' });
}
