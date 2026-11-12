"use client";

import { useParams, useRouter } from "next/navigation";

import { RequestForm } from "@/components/requests/RequestForm";
import { EmptyState } from "@/components/ui/EmptyState";
import { ErrorMessage } from "@/components/ui/ErrorMessage";
import { Spinner } from "@/components/ui/Spinner";
import { useRequest } from "@/hooks/useRequest";
import { useUpdateRequest } from "@/hooks/useRequestMutations";
import { ApiError } from "@/lib/api";

export default function EditRequestPage() {
	const params = useParams<{ id: string }>();
	const id = Number(params.id);
	const router = useRouter();
	const query = useRequest(id);
	const mutation = useUpdateRequest(id);

	if (query.isLoading)
		return (
			<div className='flex justify-center py-12'>
				<Spinner size='lg' />
			</div>
		);
	if (query.isError) return <ErrorMessage error={query.error} />;
	if (!query.data?.data.abilities.update) return <EmptyState title='This request can no longer be edited.' />;

	const request = query.data.data;
	const errors = mutation.error instanceof ApiError ? mutation.error.errors : null;
	return (
		<div className='mx-auto max-w-2xl space-y-5'>
			<h1 className='text-2xl font-semibold text-slate-900'>Edit request</h1>
			<RequestForm
				mode='edit'
				initial={{
					title: request.title,
					description: request.description,
					category: request.category,
					priority: request.priority,
				}}
				busy={mutation.isPending}
				errors={errors}
				onSave={(values) => mutation.mutate(values, { onSuccess: () => router.push(`/requests/${id}`) })}
			/>
		</div>
	);
}
