"use client";

import { createContext, useContext, useEffect, useState, type ReactNode } from "react";
import { useQueryClient } from "@tanstack/react-query";

import { apiFetch, ApiError, tokenStore, UNAUTHORIZED_EVENT } from "./api";
import type { Single, User } from "./types";

interface AuthContextValue {
	user: User | null;
	loading: boolean;
	login: (email: string, password: string) => Promise<void>;
	logout: () => Promise<void>;
}

const AuthContext = createContext<AuthContextValue | null>(null);

export function AuthProvider({ children }: { children: ReactNode }) {
	const queryClient = useQueryClient();
	const [user, setUser] = useState<User | null>(null);
	const [loading, setLoading] = useState(() => tokenStore.get() !== null);

	useEffect(() => {
		const handleUnauthorized = () => setUser(null);
		window.addEventListener(UNAUTHORIZED_EVENT, handleUnauthorized);

		const token = tokenStore.get();
		if (!token) {
			return () => window.removeEventListener(UNAUTHORIZED_EVENT, handleUnauthorized);
		} else {
			apiFetch<Single<User>>("/me")
				.then((response) => setUser(response.data))
				.catch(() => {
					tokenStore.clear();
					setUser(null);
				})
				.finally(() => setLoading(false));
		}

		return () => window.removeEventListener(UNAUTHORIZED_EVENT, handleUnauthorized);
	}, []);

	async function login(email: string, password: string) {
		const response = await apiFetch<{
			data: { token: string; user: User };
		}>("/login", { method: "POST", body: { email, password } });

		tokenStore.set(response.data.token);
		setUser(response.data.user);
	}

	async function logout() {
		try {
			await apiFetch("/logout", { method: "POST" });
		} catch (error) {
			if (!(error instanceof ApiError)) {
				throw error;
			}
		} finally {
			tokenStore.clear();
			setUser(null);
			queryClient.clear();
		}
	}

	return <AuthContext.Provider value={{ user, loading, login, logout }}>{children}</AuthContext.Provider>;
}

export function useAuth() {
	const context = useContext(AuthContext);
	if (context === null) {
		throw new Error("useAuth must be used within an AuthProvider");
	}

	return context;
}
