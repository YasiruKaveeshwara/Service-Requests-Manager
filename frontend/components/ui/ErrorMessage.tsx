import { ApiError } from "@/lib/api";

export function ErrorMessage({ error, onRetry }: { error: unknown; onRetry?: () => void }) {
	const message = error instanceof ApiError ? error.message : "Something went wrong.";
	return (
		<div className='rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700' role='alert'>
			<p>{message}</p>
			{onRetry && (
				<button className='mt-2 font-medium underline' onClick={onRetry}>
					Try again
				</button>
			)}
		</div>
	);
}
