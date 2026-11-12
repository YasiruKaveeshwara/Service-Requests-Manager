"use client";

import { useEffect, useState } from "react";
import { dismiss, subscribe, type ToastItem } from "@/lib/toast";

export function ToastViewport() {
	const [items, setItems] = useState<ToastItem[]>([]);

	useEffect(() => subscribe(setItems), []);

	return (
		<div className='fixed right-4 top-4 z-[60] flex w-80 flex-col gap-2' aria-live='polite'>
			{items.map((item) => (
				<button
					key={item.id}
					className={`rounded-md p-3 text-left text-sm shadow-lg ${
						item.kind === "error" ? "bg-red-600 text-white"
						: item.kind === "success" ? "bg-green-600 text-white"
						: "bg-slate-800 text-white"
					}`}
					onClick={() => dismiss(item.id)}>
					{item.message}
				</button>
			))}
		</div>
	);
}
