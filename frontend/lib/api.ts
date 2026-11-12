const API_URL = process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:5000/api";
const TOKEN_KEY = "service_requests_token";
export const UNAUTHORIZED_EVENT = "auth:unauthorized";

export class ApiError extends Error {
	constructor(
		public status: number,
		public code: string,
		message: string,
		public errors: Record<string, string[]> | null = null,
	) {
		super(message);
		this.name = "ApiError";
	}
}

export const tokenStore = {
	get: (): string | null => (typeof window === "undefined" ? null : window.localStorage.getItem(TOKEN_KEY)),
	set: (token: string) => window.localStorage.setItem(TOKEN_KEY, token),
	clear: () => window.localStorage.removeItem(TOKEN_KEY),
};

type Query = Record<string, string | number | boolean | null | undefined>;

export async function apiFetch<T>(
	path: string,
	options: { method?: string; body?: unknown; query?: Query } = {},
): Promise<T> {
	const url = new URL(`${API_URL}${path}`);

	for (const [key, value] of Object.entries(options.query ?? {})) {
		if (value !== undefined && value !== null && value !== "") {
			url.searchParams.set(key, String(value));
		}
	}

	const token = tokenStore.get();
	const response = await fetch(url.toString(), {
		method: options.method ?? "GET",
		headers: {
			Accept: "application/json",
			...(options.body !== undefined ? { "Content-Type": "application/json" } : {}),
			...(token ? { Authorization: `Bearer ${token}` } : {}),
		},
		body: options.body !== undefined ? JSON.stringify(options.body) : undefined,
	});

	if (response.status === 401 && path !== "/login") {
		tokenStore.clear();
		if (typeof window !== "undefined") {
			window.dispatchEvent(new Event(UNAUTHORIZED_EVENT));
		}
	}

	const json = await response.json().catch(() => null);

	if (!response.ok) {
		throw new ApiError(
			response.status,
			json?.code ?? "unknown_error",
			json?.message ?? "Request failed.",
			json?.errors ?? null,
		);
	}

	return json as T;
}
