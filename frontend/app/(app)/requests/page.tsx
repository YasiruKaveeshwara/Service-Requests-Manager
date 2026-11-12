"use client";

import Link from "next/link";
import { useState } from "react";

import { RequestFilters, type RequestFilterValue } from "@/components/requests/RequestFilters";
import { RequestTable } from "@/components/requests/RequestTable";
import { EmptyState } from "@/components/ui/EmptyState";
import { ErrorMessage } from "@/components/ui/ErrorMessage";
import { Pagination } from "@/components/ui/Pagination";
import { Spinner } from "@/components/ui/Spinner";
import { useAuth } from "@/lib/auth";
import { useDebounce } from "@/hooks/useDebounce";
import { useRequests } from "@/hooks/useRequests";

const initialFilters: RequestFilterValue = { search: "", status: "", sort: "created_at:desc" };

export default function RequestsPage() {
	const { user } = useAuth();
	const [filters, setFilters] = useState(initialFilters);
	const [page, setPage] = useState(1);
	const search = useDebounce(filters.search, 300);
	const [sort, direction] = filters.sort.split(":");
	const query = useRequests({
		search,
		status: filters.status || undefined,
		sort,
		direction,
		page,
		per_page: 15,
	});

	const title =
		user?.role === "manager" ? "Team requests"
		: user?.role === "operations" ? "Work queue"
		: "My requests";
	function updateFilters(value: RequestFilterValue) {
		setFilters(value);
		setPage(1);
	}

	return (
		<div className='space-y-5'>
			<div className='flex flex-wrap items-center justify-between gap-3'>
				<h1 className='text-2xl font-semibold text-slate-900'>{title}</h1>
				{user?.role === "employee" && (
					<Link
						className='inline-flex items-center justify-center rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700'
						href='/requests/new'>
						New request
					</Link>
				)}
			</div>
			<RequestFilters value={filters} onChange={updateFilters} />
			{query.isLoading && (
				<div className='flex justify-center py-12'>
					<Spinner size='lg' />
				</div>
			)}
			{query.isError && <ErrorMessage error={query.error} onRetry={() => void query.refetch()} />}
			{query.isSuccess && query.data.data.length === 0 && (
				<EmptyState title='No requests found.' description='Try changing your filters.' />
			)}
			{query.isSuccess && query.data.data.length > 0 && (
				<>
					<RequestTable items={query.data.data} />
					<Pagination meta={query.data.meta} onPageChange={setPage} />
				</>
			)}
		</div>
	);
}
