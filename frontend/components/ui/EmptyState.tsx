export function EmptyState({
	title,
	description,
	action,
}: {
	title: string;
	description?: string;
	action?: React.ReactNode;
}) {
	return (
		<div className='rounded-lg border border-dashed border-slate-300 p-10 text-center'>
			<h2 className='font-semibold text-slate-900'>{title}</h2>
			{description && <p className='mt-2 text-sm text-slate-500'>{description}</p>}
			{action && <div className='mt-4'>{action}</div>}
		</div>
	);
}
