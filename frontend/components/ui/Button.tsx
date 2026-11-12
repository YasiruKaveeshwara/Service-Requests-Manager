import { forwardRef } from "react";

type ButtonProps = React.ButtonHTMLAttributes<HTMLButtonElement> & {
	variant?: "primary" | "secondary" | "danger" | "ghost";
	size?: "sm" | "md";
	loading?: boolean;
};

export const Button = forwardRef<HTMLButtonElement, ButtonProps>(function Button(
	{ variant = "primary", size = "md", loading = false, className = "", children, disabled, ...props },
	ref,
) {
	const styles = {
		primary: "bg-slate-900 text-white hover:bg-slate-700",
		secondary: "border border-slate-300 bg-white text-slate-700 hover:bg-slate-50",
		danger: "bg-red-600 text-white hover:bg-red-500",
		ghost: "text-slate-600 hover:bg-slate-100",
	};
	const sizes = { sm: "px-3 py-1.5 text-sm", md: "px-4 py-2 text-sm" };

	return (
		<button
			ref={ref}
			className={`inline-flex items-center justify-center gap-2 rounded-md font-medium transition disabled:cursor-not-allowed disabled:opacity-70 ${styles[variant]} ${sizes[size]} ${className}`}
			disabled={disabled || loading}
			{...props}>
			{loading && (
				<span className='size-4 animate-spin rounded-full border-2 border-current border-t-transparent' role='status' />
			)}
			{children}
		</button>
	);
});
