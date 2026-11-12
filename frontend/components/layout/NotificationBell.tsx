"use client";

import { useEffect, useRef, useState } from "react";
import { useRouter } from "next/navigation";

import { useMarkAllNotificationsRead, useMarkNotificationRead, useNotifications } from "@/hooks/useNotifications";
import { formatDateTime } from "@/lib/format";
import { Button } from "@/components/ui/Button";

export function NotificationBell() {
	const [open, setOpen] = useState(false);
	const containerRef = useRef<HTMLDivElement>(null);
	const router = useRouter();
	const query = useNotifications();
	const markRead = useMarkNotificationRead();
	const markAll = useMarkAllNotificationsRead();
	const notifications = query.data?.data ?? [];
	const unreadCount = query.data?.meta.unread_count ?? notifications.filter((item) => item.read_at === null).length;

	useEffect(() => {
		function handlePointerDown(event: MouseEvent) {
			if (containerRef.current && !containerRef.current.contains(event.target as Node)) setOpen(false);
		}
		function handleKeyDown(event: KeyboardEvent) {
			if (event.key === "Escape") setOpen(false);
		}
		document.addEventListener("mousedown", handlePointerDown);
		document.addEventListener("keydown", handleKeyDown);
		return () => {
			document.removeEventListener("mousedown", handlePointerDown);
			document.removeEventListener("keydown", handleKeyDown);
		};
	}, []);

	function openNotification(notification: (typeof notifications)[number]) {
		markRead.mutate(notification.id);
		setOpen(false);
		if (notification.request_id !== null) router.push(`/requests/${notification.request_id}`);
	}

	return (
		<div className='relative' ref={containerRef}>
			<button
				type='button'
				aria-label='Notifications'
				className='relative rounded-md p-2 text-slate-600 hover:bg-slate-100'
				onClick={() => setOpen((current) => !current)}>
				<span aria-hidden='true'>🔔</span>
				{unreadCount > 0 && (
					<span className='absolute -right-1 -top-1 min-w-5 rounded-full bg-red-600 px-1 text-center text-xs text-white'>
						{unreadCount > 9 ? "9+" : unreadCount}
					</span>
				)}
			</button>
			{open && (
				<div className='absolute right-0 top-11 z-40 w-80 rounded-lg border border-slate-200 bg-white p-3 shadow-lg'>
					<div className='mb-2 flex items-center justify-between'>
						<h2 className='font-semibold'>Notifications</h2>
						{unreadCount > 0 && (
							<Button variant='ghost' size='sm' onClick={() => markAll.mutate()}>
								Mark all as read
							</Button>
						)}
					</div>
					{notifications.length === 0 ?
						<p className='p-4 text-sm text-slate-500'>No notifications yet.</p>
					:	<div className='max-h-96 space-y-1 overflow-y-auto'>
							{notifications.slice(0, 8).map((notification) => (
								<button
									key={notification.id}
									type='button'
									className={`block w-full rounded-md p-2 text-left text-sm hover:bg-slate-50 ${notification.read_at === null ? "font-semibold text-slate-900" : "text-slate-600"}`}
									onClick={() => openNotification(notification)}>
									<span>{notification.message}</span>
									<time className='mt-1 block text-xs font-normal text-slate-400'>
										{formatDateTime(notification.created_at)}
									</time>
								</button>
							))}
						</div>
					}
				</div>
			)}
		</div>
	);
}
