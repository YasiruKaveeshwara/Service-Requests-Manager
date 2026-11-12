import { render, screen } from "@testing-library/react";
import { describe, expect, it } from "vitest";

import { ActivityTimeline } from "@/components/requests/ActivityTimeline";

describe("ActivityTimeline", () => {
	it("renders activity text and comments", () => {
		render(
			<ActivityTimeline
				items={[
					{
						id: 1,
						event: "commented",
						from_status: null,
						to_status: null,
						comment: "Please review",
						metadata: null,
						actor: {
							id: 1,
							name: "Alex",
							email: "alex@example.com",
							role: "manager",
							role_label: "Manager",
							manager_id: null,
						},
						created_at: "2026-11-12T10:00:00Z",
					},
				]}
			/>,
		);
		expect(screen.getByText("Alex commented")).toBeInTheDocument();
		expect(screen.getByText("“Please review”")).toBeInTheDocument();
	});
});
