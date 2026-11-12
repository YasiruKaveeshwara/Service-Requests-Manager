import { forwardRef } from "react";

export const Select = forwardRef<
	HTMLSelectElement,
	React.SelectHTMLAttributes<HTMLSelectElement> & { invalid?: boolean }
>(function Select({ invalid = false, className = "", ...props }, ref) {
	return (
		<select
			ref={ref}
			className={`w-full rounded-md border bg-white px-3 py-2 text-sm text-slate-900 outline-none ring-slate-300 focus:ring-2 ${invalid ? "border-red-500" : "border-slate-300"} ${className}`}
			{...props}
		/>
	);
});
