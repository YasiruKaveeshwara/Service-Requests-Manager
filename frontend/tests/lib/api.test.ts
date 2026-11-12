import { beforeEach, describe, expect, it, vi } from "vitest";

import { apiFetch, ApiError, tokenStore, UNAUTHORIZED_EVENT } from "../../lib/api";

describe("apiFetch", () => {
	beforeEach(() => {
		vi.restoreAllMocks();
		tokenStore.clear();
		global.fetch = vi.fn().mockResolvedValue(new Response(JSON.stringify({ data: "ok" }), { status: 200 }));
	});

	it("builds query parameters and skips empty values", async () => {
		await apiFetch("/requests", {
			query: { search: "printer", page: 2, empty: "", missing: null },
		});

		expect(fetch).toHaveBeenCalledWith("http://localhost:5000/api/requests?search=printer&page=2", expect.any(Object));
	});

	it("sends a stored bearer token", async () => {
		tokenStore.set("token-123");

		await apiFetch("/me");

		expect(fetch).toHaveBeenCalledWith(
			expect.any(String),
			expect.objectContaining({
				headers: expect.objectContaining({ Authorization: "Bearer token-123" }),
			}),
		);
	});

	it("does not send authorization without a token", async () => {
		await apiFetch("/health");

		const [, options] = vi.mocked(fetch).mock.calls[0];
		expect(options?.headers).not.toHaveProperty("Authorization");
	});

	it("sends a JSON body", async () => {
		await apiFetch("/login", {
			method: "POST",
			body: { email: "user@example.com", password: "password" },
		});

		expect(fetch).toHaveBeenCalledWith(
			expect.any(String),
			expect.objectContaining({
				headers: expect.objectContaining({ "Content-Type": "application/json" }),
				body: JSON.stringify({
					email: "user@example.com",
					password: "password",
				}),
			}),
		);
	});

	it("throws ApiError for validation failures", async () => {
		vi.mocked(fetch).mockResolvedValue(
			new Response(
				JSON.stringify({
					message: "The title field is required.",
					code: "validation_failed",
					errors: { title: ["The title field is required."] },
				}),
				{ status: 422 },
			),
		);

		await expect(apiFetch("/requests")).rejects.toMatchObject({
			status: 422,
			code: "validation_failed",
			message: "The title field is required.",
			errors: { title: ["The title field is required."] },
		} satisfies Partial<ApiError>);
	});

	it("clears the token and dispatches an event for unauthorized requests", async () => {
		tokenStore.set("expired-token");
		const listener = vi.fn();
		window.addEventListener(UNAUTHORIZED_EVENT, listener);
		vi.mocked(fetch).mockResolvedValue(
			new Response(JSON.stringify({ message: "Unauthenticated.", code: "unauthenticated" }), {
				status: 401,
			}),
		);

		await expect(apiFetch("/me")).rejects.toBeInstanceOf(ApiError);

		expect(tokenStore.get()).toBeNull();
		expect(listener).toHaveBeenCalledOnce();
		window.removeEventListener(UNAUTHORIZED_EVENT, listener);
	});

	it("does not dispatch the unauthorized event for login failures", async () => {
		const listener = vi.fn();
		window.addEventListener(UNAUTHORIZED_EVENT, listener);
		vi.mocked(fetch).mockResolvedValue(
			new Response(JSON.stringify({ message: "Invalid credentials.", code: "unauthenticated" }), {
				status: 401,
			}),
		);

		await expect(apiFetch("/login", { method: "POST", body: {} })).rejects.toBeInstanceOf(ApiError);

		expect(listener).not.toHaveBeenCalled();
		window.removeEventListener(UNAUTHORIZED_EVENT, listener);
	});
});
