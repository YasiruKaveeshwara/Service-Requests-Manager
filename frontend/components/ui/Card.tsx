export function Card({
	title,
	actions,
	children,
}: {
	title?: string;
	actions?: React.ReactNode;
	children: React.ReactNode;
}) {
	return (
		<section className='rounded-lg border border-slate-200 bg-white p-5 shadow-sm'>
			{(title || actions) && (
				<header className='mb-4 flex items-center justify-between gap-4'>
					{title && <h2 className='text-lg font-semibold text-slate-900'>{title}</h2>}
					{actions}
				</header>
			)}
			{children}
		</section>
	);
}
