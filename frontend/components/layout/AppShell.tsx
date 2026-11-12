"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";

import { useAuth } from "@/lib/auth";
import { Badge } from "@/components/ui/Badge";
import { Button } from "@/components/ui/Button";
import { NotificationBell } from "@/components/layout/NotificationBell";

export function AppShell({ children }: { children: React.ReactNode }) {
	const { user, logout } = useAuth();
	const pathname = usePathname();
	const navigation = [
		{ href: "/requests", label: "Requests", visible: true },
		{ href: "/requests/new", label: "New request", visible: user?.role === "employee" },
	].filter((item) => item.visible);

	return (
		<div className='min-h-screen bg-slate-50'>
			<header className='border-b border-slate-200 bg-white shadow-sm'>
				<div className='mx-auto max-w-6xl px-4'>
					<div className='flex min-h-16 items-center justify-between gap-4'>
						<Link href='/requests' className='flex items-center gap-3'>
							<span className='flex size-9 items-center justify-center rounded-lg bg-slate-900 text-sm font-bold text-white'>
								SR
							</span>
							<span className='hidden font-semibold tracking-tight text-slate-900 sm:inline'>
								Service Requests Manager
							</span>
							<span className='font-semibold tracking-tight text-slate-900 sm:hidden'>Requests</span>
						</Link>
						<div className='flex items-center gap-2 sm:gap-3'>
							<div className='hidden items-center gap-2 border-r border-slate-200 pr-3 sm:flex'>
								<div className='text-right'>
									<p className='text-sm font-medium text-slate-900'>{user?.name}</p>
									{user && <p className='text-xs text-slate-500'>{user.role_label}</p>}
								</div>
								{user && <Badge className='bg-slate-100 text-slate-700'>{user.role_label}</Badge>}
							</div>
							<NotificationBell />
							<Button variant='ghost' size='sm' onClick={() => void logout()}>
								Logout
							</Button>
						</div>
					</div>
					<nav aria-label='Primary navigation' className='-mb-px flex gap-1 overflow-x-auto'>
						{navigation.map((item) => {
							const active =
								pathname === item.href || (item.href !== "/requests" && pathname.startsWith(`${item.href}/`));
							return (
								<Link
									key={item.href}
									href={item.href}
									aria-current={active ? "page" : undefined}
									className={`border-b-2 px-3 py-3 text-sm font-medium transition ${
										active ?
											"border-slate-900 text-slate-900"
										:	"border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-900"
									}`}>
									{item.label}
								</Link>
							);
						})}
					</nav>
					<div className='flex items-center gap-2 border-t border-slate-100 py-2 sm:hidden'>
						<span className='text-sm text-slate-700'>{user?.name}</span>
						{user && <Badge className='bg-slate-100 text-slate-700'>{user.role_label}</Badge>}
					</div>
				</div>
			</header>
			<main className='mx-auto max-w-6xl px-4 py-6'>{children}</main>
		</div>
	);
}
