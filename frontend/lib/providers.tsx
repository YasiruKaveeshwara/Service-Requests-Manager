"use client";

import { MutationCache, QueryCache, QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { useState, type ReactNode } from "react";

import { ApiError } from "./api";
import { AuthProvider } from "./auth";
import { toast } from "./toast";

export function Providers({ children }: { children: ReactNode }) {
	const [queryClient] = useState(
		() =>
			new QueryClient({
				defaultOptions: {
					queries: { retry: false, refetchOnWindowFocus: false },
				},
				queryCache: new QueryCache({
					onError: (error) => {
						if (error instanceof ApiError && (error.code === "validation_failed" || error.code === "unauthenticated")) {
							return;
						}

						toast.error(error.message);
					},
				}),
				mutationCache: new MutationCache({
					onError: (error) => {
						if (error instanceof ApiError && (error.code === "validation_failed" || error.code === "unauthenticated")) {
							return;
						}

						toast.error(error.message);
					},
				}),
			}),
	);

	return (
		<QueryClientProvider client={queryClient}>
			<AuthProvider>{children}</AuthProvider>
		</QueryClientProvider>
	);
}
