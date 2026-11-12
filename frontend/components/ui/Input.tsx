import { forwardRef } from "react";

export const Input = forwardRef<HTMLInputElement, React.InputHTMLAttributes<HTMLInputElement> & { invalid?: boolean }>(
	function Input({ invalid = false, className = "", ...props }, ref) {
		return (
			<input
				ref={ref}
				className={`w-full rounded-md border bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 outline-none ring-slate-300 focus:ring-2 ${invalid ? "border-red-500" : "border-slate-300"} ${className}`}
				{...props}
			/>
		);
	},
);
