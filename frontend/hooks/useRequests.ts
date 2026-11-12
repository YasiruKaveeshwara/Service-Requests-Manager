import { keepPreviousData, useQuery } from "@tanstack/react-query";

import { apiFetch } from "@/lib/api";
import type { Paginated, ServiceRequest } from "@/lib/types";

export type RequestQueryParams = Record<string, string | number | undefined>;

export function useRequests(params: RequestQueryParams) {
	return useQuery({
		queryKey: ["requests", params],
		queryFn: () => apiFetch<Paginated<ServiceRequest>>("/requests", { query: params }),
		placeholderData: keepPreviousData,
	});
}
