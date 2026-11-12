export function FormField({
	label,
	error,
	hint,
	children,
}: {
	label: string;
	error?: string;
	hint?: string;
	children: React.ReactNode;
}) {
	return (
		<div className='space-y-1'>
			<label className='block text-sm font-medium text-slate-700'>{label}</label>
			{children}
			{hint && !error && <p className='text-xs text-slate-500'>{hint}</p>}
			{error && (
				<p className='text-sm text-red-600' role='alert'>
					{error}
				</p>
			)}
		</div>
	);
}
