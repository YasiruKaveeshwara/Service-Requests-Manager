import { fireEvent, render, screen, waitFor } from "@testing-library/react";
import { describe, expect, it, vi } from "vitest";

import { RequestForm } from "@/components/requests/RequestForm";

const initial = {
	title: "Existing request",
	description: "Existing details",
	category: "access" as const,
	priority: "high" as const,
};

describe("RequestForm", () => {
	it("renders initial values in edit mode", () => {
		render(<RequestForm mode='edit' initial={initial} busy={false} errors={null} onSave={vi.fn()} />);
		expect(screen.getByDisplayValue("Existing request")).toBeInTheDocument();
		expect(screen.getByDisplayValue("Existing details")).toBeInTheDocument();
	});

	it("shows server errors under fields", () => {
		render(<RequestForm mode='create' busy={false} errors={{ title: ["Title is already used."] }} onSave={vi.fn()} />);
		expect(screen.getByRole("alert")).toHaveTextContent("Title is already used.");
	});

	it("saves the typed values", async () => {
		const onSave = vi.fn();
		render(<RequestForm mode='create' busy={false} errors={null} onSave={onSave} />);
		const textboxes = screen.getAllByRole("textbox");
		fireEvent.change(textboxes[0], { target: { value: "New request" } });
		fireEvent.change(textboxes[1], { target: { value: "Details" } });
		fireEvent.click(screen.getByRole("button", { name: "Save draft" }));
		await waitFor(() =>
			expect(onSave).toHaveBeenCalledWith({
				title: "New request",
				description: "Details",
				category: "it",
				priority: "medium",
			}),
		);
	});

	it("submits for review", () => {
		const onSaveAndSubmit = vi.fn();
		render(<RequestForm mode='create' busy={false} errors={null} onSave={vi.fn()} onSaveAndSubmit={onSaveAndSubmit} />);
		fireEvent.change(screen.getAllByRole("textbox")[0], { target: { value: "New request" } });
		fireEvent.change(screen.getAllByRole("textbox")[1], { target: { value: "Details" } });
		fireEvent.click(screen.getByRole("button", { name: "Submit for review" }));
		expect(onSaveAndSubmit).toHaveBeenCalled();
	});

	it("disables buttons while busy", () => {
		render(<RequestForm mode='create' busy errors={null} onSave={vi.fn()} onSaveAndSubmit={vi.fn()} />);
		expect(screen.getByRole("button", { name: "Save draft" })).toBeDisabled();
		expect(screen.getByRole("button", { name: "Submit for review" })).toBeDisabled();
	});
});
