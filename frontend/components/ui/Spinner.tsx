export function Spinner({ size = "md" }: { size?: "sm" | "md" | "lg" }) {
	const sizes = { sm: "size-4", md: "size-6", lg: "size-10" };
	return (
		<span
			className={`inline-block animate-spin rounded-full border-2 border-slate-300 border-t-slate-800 ${sizes[size]}`}
			role='status'
		/>
	);
}
