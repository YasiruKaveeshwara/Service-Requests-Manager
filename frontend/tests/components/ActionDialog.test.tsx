import { fireEvent, render, screen } from "@testing-library/react";
import { describe, expect, it, vi } from "vitest";

import { ActionDialog } from "@/components/requests/ActionDialog";
import { ApiError } from "@/lib/api";

const props = {
	open: true,
	title: "Reject request",
	confirmLabel: "Reject",
	busy: false,
	onClose: vi.fn(),
	onConfirm: vi.fn(),
};

describe("ActionDialog", () => {
	it("requires a comment when configured", () => {
		render(<ActionDialog {...props} commentRequired />);
		fireEvent.click(screen.getByRole("button", { name: "Reject" }));
		expect(screen.getByRole("alert")).toHaveTextContent("Comment is required.");
		expect(props.onConfirm).not.toHaveBeenCalled();
	});

	it("confirms with a comment", () => {
		render(<ActionDialog {...props} />);
		fireEvent.change(screen.getByLabelText("Comment"), { target: { value: "Needs changes" } });
		fireEvent.click(screen.getByRole("button", { name: "Reject" }));
		expect(props.onConfirm).toHaveBeenCalledWith({ comment: "Needs changes", assigneeId: undefined });
	});

	it("confirms with an assignee", () => {
		render(
			<ActionDialog
				{...props}
				assignees={[
					{
						id: 2,
						name: "Ops",
						email: "ops@example.com",
						role: "operations",
						role_label: "Operations",
						manager_id: null,
					},
				]}
			/>,
		);
		fireEvent.change(screen.getByLabelText("Owner"), { target: { value: "2" } });
		fireEvent.click(screen.getByRole("button", { name: "Reject" }));
		expect(props.onConfirm).toHaveBeenCalledWith({ comment: undefined, assigneeId: 2 });
	});

	it("renders server errors", () => {
		render(
			<ActionDialog
				{...props}
				error={new ApiError(422, "validation_failed", "Invalid action.", { comment: ["Comment is invalid."] })}
			/>,
		);
		expect(screen.getAllByRole("alert")[0]).toHaveTextContent("Invalid action.");
		expect(screen.getByText("Comment is invalid.")).toBeInTheDocument();
	});
});
