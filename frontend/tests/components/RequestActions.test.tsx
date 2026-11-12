import { fireEvent, render, screen } from "@testing-library/react";
import { describe, expect, it, vi } from "vitest";

import { RequestActions } from "@/components/requests/RequestActions";
import type { Abilities } from "@/lib/types";

const makeAbilities = (overrides: Partial<Abilities> = {}): Abilities => ({
	update: false,
	submit: false,
	cancel: false,
	decide: false,
	assign: false,
	start: false,
	complete: false,
	comment: false,
	...overrides,
});

describe("RequestActions", () => {
	it("shows employee actions", () => {
		render(
			<RequestActions abilities={makeAbilities({ update: true, submit: true, cancel: true })} onAction={vi.fn()} />,
		);
		expect(screen.getByText("Edit")).toBeInTheDocument();
		expect(screen.getByText("Submit for review")).toBeInTheDocument();
		expect(screen.getByText("Withdraw")).toBeInTheDocument();
	});

	it("shows manager decisions", () => {
		render(<RequestActions abilities={makeAbilities({ decide: true })} onAction={vi.fn()} />);
		expect(screen.getByText("Approve")).toBeInTheDocument();
		expect(screen.getByText("Return")).toBeInTheDocument();
		expect(screen.getByText("Reject")).toBeInTheDocument();
	});

	it("shows operations actions", () => {
		render(
			<RequestActions abilities={makeAbilities({ assign: true, start: true, complete: true })} onAction={vi.fn()} />,
		);
		expect(screen.getByText("Assign owner")).toBeInTheDocument();
		expect(screen.getByRole("combobox", { name: "Change status" })).toBeInTheDocument();
		expect(screen.getByRole("option", { name: "In progress" })).toBeInTheDocument();
		expect(screen.getByRole("option", { name: "Completed" })).toBeInTheDocument();
	});

	it("renders nothing when no action is allowed", () => {
		const { container } = render(<RequestActions abilities={makeAbilities()} onAction={vi.fn()} />);
		expect(container).toBeEmptyDOMElement();
	});

	it("reports the clicked action key", () => {
		const onAction = vi.fn();
		render(<RequestActions abilities={makeAbilities({ submit: true })} onAction={onAction} />);
		fireEvent.click(screen.getByText("Submit for review"));
		expect(onAction).toHaveBeenCalledWith("submit");
	});

	it("disables actions while busy", () => {
		render(<RequestActions abilities={makeAbilities({ submit: true })} onAction={vi.fn()} busy />);
		expect(screen.getByRole("button", { name: "Submit for review" })).toBeDisabled();
	});
});
