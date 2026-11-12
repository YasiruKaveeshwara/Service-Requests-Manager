"use client";

import { useEffect } from "react";
import { useRouter } from "next/navigation";

import { LoginForm } from "@/components/auth/LoginForm";
import { useAuth } from "@/lib/auth";

export default function LoginPage() {
	const { user, loading, login } = useAuth();
	const router = useRouter();

	useEffect(() => {
		if (!loading && user) router.replace("/requests");
	}, [loading, router, user]);

	if (loading || user) return null;

	return (
		<main className='flex min-h-screen items-center justify-center bg-slate-50 p-4'>
			<LoginForm
				onSubmit={async (email, password) => {
					await login(email, password);
					router.replace("/requests");
				}}
			/>
		</main>
	);
}
