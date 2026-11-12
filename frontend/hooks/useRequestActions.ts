import { useMutation, useQueryClient } from "@tanstack/react-query";

import { apiFetch } from "@/lib/api";
import { toast } from "@/lib/toast";
import type { RequestStatus, ServiceRequest, Single } from "@/lib/types";

export function useRequestAction<TVars = void>(
	id: number,
	build: (vars: TVars) => { path: string; body?: unknown },
	successMessage: string,
) {
	const queryClient = useQueryClient();
	return useMutation({
		mutationFn: (vars: TVars) => {
			const { path, body } = build(vars);
			return apiFetch<Single<ServiceRequest>>(path, { method: "POST", body: body ?? {} });
		},
		onSuccess: () => {
			queryClient.invalidateQueries({ queryKey: ["requests"] });
			queryClient.invalidateQueries({ queryKey: ["request", id] });
			queryClient.invalidateQueries({ queryKey: ["notifications"] });
			toast.success(successMessage);
		},
	});
}

export function useSubmitRequest(id: number) {
	return useRequestAction(id, () => ({ path: `/requests/${id}/submit` }), "Request submitted.");
}

export function useCancelRequest(id: number) {
	return useRequestAction(
		id,
		(vars: { comment?: string }) => ({ path: `/requests/${id}/cancel`, body: vars }),
		"Request withdrawn.",
	);
}

export function useDecideRequest(id: number) {
	return useRequestAction(
		id,
		(vars: { decision: string; comment?: string }) => ({ path: `/requests/${id}/decision`, body: vars }),
		"Decision saved.",
	);
}

export function useAssignRequest(id: number) {
	return useRequestAction(
		id,
		(vars: { assigneeId: number; comment?: string }) => ({
			path: `/requests/${id}/assign`,
			body: { assignee_id: vars.assigneeId, comment: vars.comment },
		}),
		"Request assigned.",
	);
}

export function useChangeStatus(id: number) {
	return useRequestAction(
		id,
		(vars: { status: RequestStatus; comment?: string }) => ({ path: `/requests/${id}/status`, body: vars }),
		"Request status updated.",
	);
}

export function useAddComment(id: number) {
	return useRequestAction(
		id,
		(vars: { body: string }) => ({ path: `/requests/${id}/comments`, body: vars }),
		"Comment added.",
	);
}
