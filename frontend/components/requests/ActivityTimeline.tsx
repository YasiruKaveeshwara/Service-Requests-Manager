import { describeActivity } from "@/lib/activity";
import { formatDateTime } from "@/lib/format";
import type { ActivityLog } from "@/lib/types";

export function ActivityTimeline({ items }: { items: ActivityLog[] }) {
	return (
		<ol className='space-y-5'>
			{items.map((item) => (
				<li key={item.id} className='relative pl-5'>
					<span className='absolute left-0 top-1.5 size-2 rounded-full bg-slate-400' />
					<p className='text-sm text-slate-700'>{describeActivity(item)}</p>
					{item.comment && (
						<blockquote className='mt-1 border-l-2 border-slate-200 pl-3 text-sm italic text-slate-500'>
							“{item.comment}”
						</blockquote>
					)}
					<time className='mt-1 block text-xs text-slate-400'>{formatDateTime(item.created_at)}</time>
				</li>
			))}
		</ol>
	);
}
