import { fireEvent, render, screen } from "@testing-library/react";
import { describe, expect, it, vi } from "vitest";

import { RequestFilters } from "@/components/requests/RequestFilters";

describe("RequestFilters", () => {
	it("notifies when the status changes", () => {
		const onChange = vi.fn();
		render(<RequestFilters value={{ search: "", status: "", sort: "created_at:desc" }} onChange={onChange} />);
		fireEvent.change(screen.getByLabelText("Filter by status"), { target: { value: "submitted" } });
		expect(onChange).toHaveBeenCalledWith({ search: "", status: "submitted", sort: "created_at:desc" });
	});
});
