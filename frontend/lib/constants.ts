import type { Category, Priority, RequestStatus } from "./types";

export const STATUS_OPTIONS: { value: RequestStatus; label: string }[] = [
	{ value: "draft", label: "Draft" },
	{ value: "submitted", label: "Submitted" },
	{ value: "returned", label: "Returned" },
	{ value: "approved", label: "Approved" },
	{ value: "rejected", label: "Rejected" },
	{ value: "in_progress", label: "In progress" },
	{ value: "completed", label: "Completed" },
	{ value: "cancelled", label: "Cancelled" },
];

export const CATEGORY_OPTIONS: { value: Category; label: string }[] = [
	{ value: "it", label: "IT" },
	{ value: "equipment", label: "Equipment" },
	{ value: "maintenance", label: "Maintenance" },
	{ value: "access", label: "Access" },
];

export const PRIORITY_OPTIONS: { value: Priority; label: string }[] = [
	{ value: "low", label: "Low" },
	{ value: "medium", label: "Medium" },
	{ value: "high", label: "High" },
];

export const STATUS_STYLES: Record<RequestStatus, string> = {
	draft: "bg-slate-100 text-slate-700",
	submitted: "bg-blue-100 text-blue-700",
	returned: "bg-amber-100 text-amber-700",
	approved: "bg-emerald-100 text-emerald-700",
	in_progress: "bg-indigo-100 text-indigo-700",
	completed: "bg-green-100 text-green-700",
	rejected: "bg-red-100 text-red-700",
	cancelled: "bg-gray-100 text-gray-700",
};

export const PRIORITY_STYLES: Record<Priority, string> = {
	low: "bg-slate-100 text-slate-700",
	medium: "bg-amber-100 text-amber-700",
	high: "bg-red-100 text-red-700",
};

export const DEMO_ACCOUNTS = [
	{ label: "Employee", email: "employee1@service-requests.test", password: "password" },
	{ label: "Manager", email: "manager@service-requests.test", password: "password" },
	{ label: "Operations", email: "ops1@service-requests.test", password: "password" },
];
