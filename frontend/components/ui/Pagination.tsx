import { Button } from "./Button";
import type { PaginationMeta } from "@/lib/types";

export function Pagination({ meta, onPageChange }: { meta: PaginationMeta; onPageChange: (page: number) => void }) {
	return (
		<div className='flex items-center justify-between gap-4 text-sm text-slate-600'>
			<span>
				Page {meta.current_page} of {meta.last_page} ({meta.total} total)
			</span>
			<div className='flex gap-2'>
				<Button
					variant='secondary'
					size='sm'
					disabled={meta.current_page <= 1}
					onClick={() => onPageChange(meta.current_page - 1)}>
					Previous
				</Button>
				<Button
					variant='secondary'
					size='sm'
					disabled={meta.current_page >= meta.last_page}
					onClick={() => onPageChange(meta.current_page + 1)}>
					Next
				</Button>
			</div>
		</div>
	);
}
