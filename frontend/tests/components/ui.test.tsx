import { fireEvent, render, screen } from "@testing-library/react";
import { describe, expect, it, vi } from "vitest";

import { Button } from "@/components/ui/Button";
import { FormField } from "@/components/ui/FormField";
import { Modal } from "@/components/ui/Modal";
import { Pagination } from "@/components/ui/Pagination";
import { StatusBadge } from "@/components/requests/StatusBadge";

describe("UI primitives", () => {
	it("renders status labels and styles", () => {
		const { rerender } = render(<StatusBadge status='in_progress' />);
		expect(screen.getByText("In progress")).toHaveClass("bg-indigo-100");
		rerender(<StatusBadge status='rejected' />);
		expect(screen.getByText("Rejected")).toHaveClass("bg-red-100");
	});

	it("handles pagination boundaries and changes", () => {
		const onPageChange = vi.fn();
		const { rerender } = render(
			<Pagination meta={{ current_page: 1, last_page: 2, per_page: 15, total: 20 }} onPageChange={onPageChange} />,
		);
		expect(screen.getByRole("button", { name: "Previous" })).toBeDisabled();
		fireEvent.click(screen.getByRole("button", { name: "Next" }));
		expect(onPageChange).toHaveBeenCalledWith(2);
		rerender(
			<Pagination meta={{ current_page: 2, last_page: 2, per_page: 15, total: 20 }} onPageChange={onPageChange} />,
		);
		expect(screen.getByRole("button", { name: "Next" })).toBeDisabled();
	});

	it("shows a form error", () => {
		render(
			<FormField label='Title' error='Title is required'>
				<input />
			</FormField>,
		);
		expect(screen.getByRole("alert")).toHaveTextContent("Title is required");
	});

	it("disables and spins while loading", () => {
		render(<Button loading>Save</Button>);
		expect(screen.getByRole("button")).toBeDisabled();
		expect(screen.getByRole("status")).toBeInTheDocument();
	});

	it("closes a modal on Escape", () => {
		const onClose = vi.fn();
		render(
			<Modal open title='Confirm' onClose={onClose}>
				Body
			</Modal>,
		);
		fireEvent.keyDown(window, { key: "Escape" });
		expect(onClose).toHaveBeenCalledOnce();
	});
});
