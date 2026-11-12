"use client";

import { STATUS_OPTIONS } from "@/lib/constants";
import { Input } from "@/components/ui/Input";
import { Select } from "@/components/ui/Select";

export interface RequestFilterValue {
	search: string;
	status: string;
	sort: string;
}

export function RequestFilters({
	value,
	onChange,
}: {
	value: RequestFilterValue;
	onChange: (value: RequestFilterValue) => void;
}) {
	return (
		<div className='grid gap-3 rounded-lg border border-slate-200 bg-white p-4 md:grid-cols-3'>
			<Input
				aria-label='Search requests'
				placeholder='Search requests'
				value={value.search}
				onChange={(event) => onChange({ ...value, search: event.target.value })}
			/>
			<Select
				aria-label='Filter by status'
				value={value.status}
				onChange={(event) => onChange({ ...value, status: event.target.value })}>
				<option value=''>All statuses</option>
				{STATUS_OPTIONS.map((option) => (
					<option key={option.value} value={option.value}>
						{option.label}
					</option>
				))}
			</Select>
			<Select
				aria-label='Sort requests'
				value={value.sort}
				onChange={(event) => onChange({ ...value, sort: event.target.value })}>
				<option value='created_at:desc'>Newest</option>
				<option value='created_at:asc'>Oldest</option>
				<option value='title:asc'>Title A to Z</option>
				<option value='title:desc'>Title Z to A</option>
			</Select>
		</div>
	);
}
