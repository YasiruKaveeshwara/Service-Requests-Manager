import { describe, expect, it } from "vitest";

import { describeActivity } from "@/lib/activity";
import type { ActivityLog } from "@/lib/types";

const base = {
	id: 1,
	from_status: null,
	to_status: null,
	comment: null,
	metadata: null,
	actor: {
		id: 1,
		name: "Alex",
		email: "alex@example.com",
		role: "manager" as const,
		role_label: "Manager",
		manager_id: null,
	},
	created_at: "2026-11-12T10:00:00Z",
};
const log = (event: ActivityLog["event"], values: Partial<ActivityLog> = {}): ActivityLog => ({
	...base,
	event,
	...values,
});

describe("describeActivity", () => {
	it("describes created", () => expect(describeActivity(log("created"))).toBe("Alex created the request"));
	it("describes updated", () => expect(describeActivity(log("updated"))).toBe("Alex edited the request"));
	it("describes transitioned", () =>
		expect(describeActivity(log("transitioned", { from_status: "submitted", to_status: "approved" }))).toBe(
			"Alex moved the request from Submitted to Approved",
		));
	it("describes assigned", () =>
		expect(describeActivity(log("assigned", { metadata: { to_assignee_name: "Sam" } }))).toBe(
			"Alex assigned the request to Sam",
		));
	it("describes reassigned", () =>
		expect(describeActivity(log("reassigned", { metadata: { to_assignee_name: "Sam" } }))).toBe(
			"Alex reassigned the request to Sam",
		));
	it("describes commented", () => expect(describeActivity(log("commented"))).toBe("Alex commented"));
});
