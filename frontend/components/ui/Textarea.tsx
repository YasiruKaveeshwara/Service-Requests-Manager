import { forwardRef } from "react";

export const Textarea = forwardRef<
	HTMLTextAreaElement,
	React.TextareaHTMLAttributes<HTMLTextAreaElement> & { invalid?: boolean }
>(function Textarea({ invalid = false, className = "", ...props }, ref) {
	return (
		<textarea
			ref={ref}
			className={`w-full rounded-md border bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 outline-none ring-slate-300 focus:ring-2 ${invalid ? "border-red-500" : "border-slate-300"} ${className}`}
			{...props}
		/>
	);
});
