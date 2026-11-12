import { render, screen } from "@testing-library/react";
import { describe, expect, it, vi } from "vitest";

import Home from "../app/page";

vi.mock("../lib/auth", () => ({
	useAuth: () => ({ user: null, loading: true }),
}));
vi.mock("next/navigation", () => ({
	useRouter: () => ({ replace: vi.fn() }),
}));

describe("home page", () => {
	it("renders a loading state while checking authentication", () => {
		render(<Home />);

		expect(screen.getByRole("status")).toBeInTheDocument();
	});
});
