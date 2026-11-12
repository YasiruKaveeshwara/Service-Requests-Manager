export type Role = "employee" | "manager" | "operations";
export type RequestStatus =
	| "draft"
	| "submitted"
	| "returned"
	| "approved"
	| "rejected"
	| "in_progress"
	| "completed"
	| "cancelled";
export type Category = "it" | "equipment" | "maintenance" | "access";
export type Priority = "low" | "medium" | "high";
export type ActivityEventType = "created" | "updated" | "transitioned" | "assigned" | "reassigned" | "commented";

export interface User {
	id: number;
	name: string;
	email: string;
	role: Role;
	role_label: string;
	manager_id: number | null;
}

export interface Abilities {
	update: boolean;
	submit: boolean;
	cancel: boolean;
	decide: boolean;
	assign: boolean;
	start: boolean;
	complete: boolean;
	comment: boolean;
}

export interface Comment {
	id: number;
	body: string;
	user: User | null;
	created_at: string;
}

export interface ActivityLog {
	id: number;
	event: ActivityEventType;
	from_status: RequestStatus | null;
	to_status: RequestStatus | null;
	comment: string | null;
	metadata: Record<string, unknown> | null;
	actor: User | null;
	created_at: string;
}

export interface ServiceRequest {
	id: number;
	title: string;
	description: string;
	category: Category;
	category_label: string;
	priority: Priority;
	priority_label: string;
	status: RequestStatus;
	status_label: string;
	requester: User | null;
	assignee: User | null;
	submitted_at: string | null;
	completed_at: string | null;
	created_at: string;
	updated_at: string;
	comments?: Comment[];
	activity?: ActivityLog[];
	abilities: Abilities;
}

export interface AppNotification {
	id: string;
	type: string | null;
	message: string;
	request_id: number | null;
	read_at: string | null;
	created_at: string;
}

export interface PaginationMeta {
	current_page: number;
	last_page: number;
	per_page: number;
	total: number;
	unread_count?: number;
}

export interface Paginated<T> {
	data: T[];
	links: Record<string, string | null>;
	meta: PaginationMeta;
}

export interface Single<T> {
	data: T;
}
