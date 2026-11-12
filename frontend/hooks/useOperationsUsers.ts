import { useQuery } from "@tanstack/react-query";

import { apiFetch } from "@/lib/api";
import type { User } from "@/lib/types";

export function useOperationsUsers(enabled: boolean) {
	return useQuery({
		queryKey: ["users", "operations"],
		queryFn: () => apiFetch<{ data: User[] }>("/users", { query: { role: "operations" } }),
		enabled,
	});
}
