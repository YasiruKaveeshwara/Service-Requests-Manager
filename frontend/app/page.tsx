"use client";

import { useEffect } from "react";
import { useRouter } from "next/navigation";

import { Spinner } from "@/components/ui/Spinner";
import { useAuth } from "@/lib/auth";

export default function Home() {
	const { user, loading } = useAuth();
	const router = useRouter();

	useEffect(() => {
		if (!loading) router.replace(user ? "/requests" : "/login");
	}, [loading, router, user]);

	return (
		<div className='flex min-h-screen items-center justify-center'>
			<Spinner size='lg' />
		</div>
	);
}
