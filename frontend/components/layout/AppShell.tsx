"use client";

import { useAuth } from "@/lib/auth";
import { Badge } from "@/components/ui/Badge";
import { Button } from "@/components/ui/Button";

export function AppShell({ children }: { children: React.ReactNode }) {
	const { user, logout } = useAuth();

	return (
		<div className='min-h-screen bg-slate-50'>
			<header className='border-b border-slate-200 bg-white'>
				<div className='mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-4'>
					<span className='font-semibold text-slate-900'>Service Requests Manager</span>
					<div className='flex items-center gap-3'>
						<span className='hidden text-sm text-slate-600 sm:inline'>{user?.name}</span>
						{user && <Badge className='bg-slate-100 text-slate-700'>{user.role_label}</Badge>}
						<span className='text-sm text-slate-400' aria-label='Notifications'>
							Notifications
						</span>
						<Button variant='ghost' size='sm' onClick={() => void logout()}>
							Logout
						</Button>
					</div>
				</div>
			</header>
			<main className='mx-auto max-w-6xl px-4 py-6'>{children}</main>
		</div>
	);
}
