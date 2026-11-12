"use client";

import { useEffect } from "react";

export function Modal({
	open,
	title,
	onClose,
	children,
	footer,
}: {
	open: boolean;
	title: string;
	onClose: () => void;
	children: React.ReactNode;
	footer?: React.ReactNode;
}) {
	useEffect(() => {
		if (!open) return;
		const handleKeyDown = (event: KeyboardEvent) => {
			if (event.key === "Escape") onClose();
		};
		window.addEventListener("keydown", handleKeyDown);
		return () => window.removeEventListener("keydown", handleKeyDown);
	}, [open, onClose]);

	if (!open) return null;

	return (
		<div className='fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4' role='presentation'>
			<div
				className='w-full max-w-lg rounded-lg bg-white p-6 shadow-xl'
				role='dialog'
				aria-modal='true'
				aria-labelledby='modal-title'>
				<div className='flex items-center justify-between gap-4'>
					<h2 id='modal-title' className='text-lg font-semibold'>
						{title}
					</h2>
					<button aria-label='Close' className='text-xl text-slate-500' onClick={onClose}>
						×
					</button>
				</div>
				<div className='mt-4'>{children}</div>
				{footer && <div className='mt-6 flex justify-end gap-2'>{footer}</div>}
			</div>
		</div>
	);
}
