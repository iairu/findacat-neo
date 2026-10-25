/** Thin client for the Laravel API. Same origin, cookie session (Sanctum SPA mode). */

export class ApiError extends Error {
  constructor(
    public status: number,
    message: string,
    public errors: Record<string, string[]> = {},
  ) {
    super(message);
  }
}

function cookie(name: string): string | null {
  const hit = document.cookie.split('; ').find((c) => c.startsWith(name + '='));
  return hit ? decodeURIComponent(hit.slice(name.length + 1)) : null;
}

let csrfReady = false;

export async function api<T = any>(method: string, path: string, body?: unknown): Promise<T> {
  const mutating = method !== 'GET';
  if (mutating && !csrfReady) {
    await fetch('/api/sanctum/csrf-cookie', { credentials: 'same-origin' });
    csrfReady = true;
  }
  const headers: Record<string, string> = { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' };
  if (body !== undefined) headers['Content-Type'] = 'application/json';
  const token = mutating ? cookie('XSRF-TOKEN') : null;
  if (token) headers['X-XSRF-TOKEN'] = token;

  const res = await fetch('/api' + path, {
    method,
    headers,
    credentials: 'same-origin',
    body: body === undefined ? undefined : JSON.stringify(body),
  });
  if (res.status === 204) return undefined as T;
  const data = await res.json().catch(() => ({}));
  if (!res.ok) throw new ApiError(res.status, data.message ?? res.statusText, data.errors ?? {});
  return data as T;
}

export const query = (params: Record<string, string | number | boolean | undefined | null>): string => {
  const q = new URLSearchParams();
  for (const [k, v] of Object.entries(params)) if (v !== undefined && v !== null && v !== '') q.set(k, String(v));
  const s = q.toString();
  return s ? '?' + s : '';
};
