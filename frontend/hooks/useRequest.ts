import { useQuery } from "@tanstack/react-query";

import { apiFetch } from "@/lib/api";
import type { ServiceRequest, Single } from "@/lib/types";

export function useRequest(id: number) {
	return useQuery({
		queryKey: ["request", id],
		queryFn: () => apiFetch<Single<ServiceRequest>>(`/requests/${id}`),
		enabled: id > 0,
	});
}
