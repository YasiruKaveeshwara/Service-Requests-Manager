import { useMutation, useQueryClient } from "@tanstack/react-query";

import { apiFetch } from "@/lib/api";
import { toast } from "@/lib/toast";
import type { ServiceRequest, ServiceRequestInput, Single } from "@/lib/types";

export function useCreateRequest() {
	const queryClient = useQueryClient();

	return useMutation({
		mutationFn: (values: ServiceRequestInput & { submit?: boolean }) =>
			apiFetch<Single<ServiceRequest>>("/requests", { method: "POST", body: values }),
		onSuccess: (response) => {
			queryClient.invalidateQueries({ queryKey: ["requests"] });
			queryClient.invalidateQueries({ queryKey: ["request", response.data.id] });
			toast.success("Request created.");
		},
	});
}

export function useUpdateRequest(id: number) {
	const queryClient = useQueryClient();

	return useMutation({
		mutationFn: (values: ServiceRequestInput) =>
			apiFetch<Single<ServiceRequest>>(`/requests/${id}`, { method: "PUT", body: values }),
		onSuccess: () => {
			queryClient.invalidateQueries({ queryKey: ["requests"] });
			queryClient.invalidateQueries({ queryKey: ["request", id] });
			toast.success("Request updated.");
		},
	});
}
