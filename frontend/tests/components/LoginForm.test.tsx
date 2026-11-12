import { fireEvent, render, screen, waitFor } from "@testing-library/react";
import { describe, expect, it, vi } from "vitest";

import { LoginForm } from "@/components/auth/LoginForm";
import { ApiError } from "@/lib/api";

describe("LoginForm", () => {
	it("renders email and password fields", () => {
		render(<LoginForm onSubmit={vi.fn()} />);
		expect(screen.getByLabelText("Email")).toBeInTheDocument();
		expect(screen.getByLabelText("Password")).toBeInTheDocument();
	});

	it("fills credentials from a demo account", () => {
		render(<LoginForm onSubmit={vi.fn()} />);
		fireEvent.click(screen.getByRole("button", { name: "Employee: employee1@service-requests.test" }));
		expect(screen.getByLabelText("Email")).toHaveValue("employee1@service-requests.test");
		expect(screen.getByLabelText("Password")).toHaveValue("password");
	});

	it("submits the entered credentials", async () => {
		const onSubmit = vi.fn().mockResolvedValue(undefined);
		render(<LoginForm onSubmit={onSubmit} />);
		fireEvent.change(screen.getByLabelText("Email"), { target: { value: "user@example.com" } });
		fireEvent.change(screen.getByLabelText("Password"), { target: { value: "secret" } });
		fireEvent.click(screen.getByRole("button", { name: "Sign in" }));
		await waitFor(() => expect(onSubmit).toHaveBeenCalledWith("user@example.com", "secret"));
	});

	it("shows an authentication error", async () => {
		const onSubmit = vi.fn().mockRejectedValue(new ApiError(401, "unauthenticated", "Invalid email or password."));
		render(<LoginForm onSubmit={onSubmit} />);
		fireEvent.change(screen.getByLabelText("Email"), { target: { value: "user@example.com" } });
		fireEvent.change(screen.getByLabelText("Password"), { target: { value: "wrong" } });
		fireEvent.click(screen.getByRole("button", { name: "Sign in" }));
		await waitFor(() => expect(screen.getByRole("alert")).toHaveTextContent("Invalid email or password."));
	});
});
