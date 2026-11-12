import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";

import { apiFetch } from "@/lib/api";
import type { AppNotification, Paginated } from "@/lib/types";

export function useNotifications() {
	return useQuery({
		queryKey: ["notifications"],
		queryFn: () => apiFetch<Paginated<AppNotification>>("/notifications"),
		refetchInterval: 30000,
	});
}

export function useMarkNotificationRead() {
	const queryClient = useQueryClient();
	return useMutation({
		mutationFn: (id: string) => apiFetch(`/notifications/${id}/read`, { method: "POST" }),
		onSuccess: () => queryClient.invalidateQueries({ queryKey: ["notifications"] }),
	});
}

export function useMarkAllNotificationsRead() {
	const queryClient = useQueryClient();
	return useMutation({
		mutationFn: () => apiFetch("/notifications/read-all", { method: "POST" }),
		onSuccess: () => queryClient.invalidateQueries({ queryKey: ["notifications"] }),
	});
}
