import Link from "next/link";

import { formatDateTime, humanize } from "@/lib/format";
import type { ServiceRequest } from "@/lib/types";
import { PriorityBadge } from "./PriorityBadge";
import { StatusBadge } from "./StatusBadge";

export function RequestTable({ items }: { items: ServiceRequest[] }) {
	return (
		<div className='overflow-x-auto rounded-lg border border-slate-200 bg-white'>
			<table className='min-w-full text-left text-sm'>
				<thead className='border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500'>
					<tr>
						<th className='px-4 py-3'>ID</th>
						<th className='px-4 py-3'>Title</th>
						<th className='px-4 py-3'>Category</th>
						<th className='px-4 py-3'>Priority</th>
						<th className='px-4 py-3'>Status</th>
						<th className='px-4 py-3'>Requester</th>
						<th className='px-4 py-3'>Assignee</th>
						<th className='px-4 py-3'>Created</th>
					</tr>
				</thead>
				<tbody className='divide-y divide-slate-100'>
					{items.map((item) => (
						<tr key={item.id} className='text-slate-700'>
							<td className='px-4 py-3'>{item.id}</td>
							<td className='px-4 py-3 font-medium'>
								<Link className='text-slate-900 hover:underline' href={`/requests/${item.id}`}>
									{item.title}
								</Link>
							</td>
							<td className='px-4 py-3'>{item.category_label || humanize(item.category)}</td>
							<td className='px-4 py-3'>
								<PriorityBadge priority={item.priority} />
							</td>
							<td className='px-4 py-3'>
								<StatusBadge status={item.status} />
							</td>
							<td className='px-4 py-3'>{item.requester?.name ?? "-"}</td>
							<td className='px-4 py-3'>{item.assignee?.name ?? "-"}</td>
							<td className='whitespace-nowrap px-4 py-3'>{formatDateTime(item.created_at)}</td>
						</tr>
					))}
				</tbody>
			</table>
		</div>
	);
}
