export type ToastKind = "success" | "error" | "info";

export interface ToastItem {
	id: number;
	kind: ToastKind;
	message: string;
}

type Listener = (items: ToastItem[]) => void;

let items: ToastItem[] = [];
let nextId = 1;
const listeners = new Set<Listener>();
const emit = () => listeners.forEach((listener) => listener(items));

function push(kind: ToastKind, message: string) {
	const id = nextId++;
	items = [...items, { id, kind, message }];
	emit();
	setTimeout(() => dismiss(id), 5000);
}

export function dismiss(id: number) {
	items = items.filter((item) => item.id !== id);
	emit();
}

export function subscribe(listener: Listener) {
	listeners.add(listener);
	listener(items);
	return () => {
		listeners.delete(listener);
	};
}

export const toast = {
	success: (message: string) => push("success", message),
	error: (message: string) => push("error", message),
	info: (message: string) => push("info", message),
};
