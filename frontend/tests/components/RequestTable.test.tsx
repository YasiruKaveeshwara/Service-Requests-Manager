import { render, screen } from "@testing-library/react";
import { describe, expect, it } from "vitest";

import { RequestTable } from "@/components/requests/RequestTable";
import type { ServiceRequest } from "@/lib/types";

const request: ServiceRequest = {
	id: 12,
	title: "Laptop request",
	description: "A laptop",
	category: "equipment",
	category_label: "Equipment",
	priority: "high",
	priority_label: "High",
	status: "submitted",
	status_label: "Submitted",
	requester: {
		id: 1,
		name: "Employee",
		email: "employee@example.com",
		role: "employee",
		role_label: "Employee",
		manager_id: null,
	},
	assignee: null,
	submitted_at: "2026-11-12T10:00:00Z",
	completed_at: null,
	created_at: "2026-11-12T10:00:00Z",
	updated_at: "2026-11-12T10:00:00Z",
	abilities: {
		update: false,
		submit: false,
		cancel: false,
		decide: false,
		assign: false,
		start: false,
		complete: false,
		comment: false,
	},
};

describe("RequestTable", () => {
	it("renders a detail link and a dash for an unassigned request", () => {
		render(<RequestTable items={[request]} />);
		expect(screen.getByRole("link", { name: "Laptop request" })).toHaveAttribute("href", "/requests/12");
		expect(screen.getAllByText("-")).toHaveLength(1);
	});
});
