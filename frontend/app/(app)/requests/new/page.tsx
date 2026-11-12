"use client";

import { useRouter } from "next/navigation";

import { RequestForm } from "@/components/requests/RequestForm";
import { EmptyState } from "@/components/ui/EmptyState";
import { useAuth } from "@/lib/auth";
import { useCreateRequest } from "@/hooks/useRequestMutations";
import { ApiError } from "@/lib/api";

export default function NewRequestPage() {
	const { user } = useAuth();
	const router = useRouter();
	const mutation = useCreateRequest();

	if (user?.role !== "employee") {
		return <EmptyState title='Only employees can create requests.' />;
	}

	const errors = mutation.error instanceof ApiError ? mutation.error.errors : null;
	return (
		<div className='mx-auto max-w-2xl space-y-5'>
			<h1 className='text-2xl font-semibold text-slate-900'>New request</h1>
			<RequestForm
				mode='create'
				busy={mutation.isPending}
				errors={errors}
				onSave={(values) =>
					mutation.mutate(values, { onSuccess: (response) => router.push(`/requests/${response.data.id}`) })
				}
				onSaveAndSubmit={(values) =>
					mutation.mutate(
						{ ...values, submit: true },
						{ onSuccess: (response) => router.push(`/requests/${response.data.id}`) },
					)
				}
			/>
		</div>
	);
}
