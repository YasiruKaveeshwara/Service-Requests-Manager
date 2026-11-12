import { fireEvent, render, screen } from "@testing-library/react";
import { beforeEach, describe, expect, it, vi } from "vitest";
import type { AppNotification, Paginated } from "@/lib/types";

const mockPush = vi.fn();
const mockRead = vi.fn();
const mockAll = vi.fn();
let mockData: Paginated<AppNotification> = {
	data: [],
	meta: { current_page: 1, last_page: 1, per_page: 15, total: 0, unread_count: 0 },
	links: {},
};

vi.mock("next/navigation", () => ({ useRouter: () => ({ push: mockPush }) }));
vi.mock("@/hooks/useNotifications", () => ({
	useNotifications: () => ({ data: mockData }),
	useMarkNotificationRead: () => ({ mutate: mockRead }),
	useMarkAllNotificationsRead: () => ({ mutate: mockAll }),
}));

import { NotificationBell } from "@/components/layout/NotificationBell";

describe("NotificationBell", () => {
	beforeEach(() => {
		mockPush.mockReset();
		mockRead.mockReset();
		mockAll.mockReset();
		mockData = {
			data: [],
			meta: { current_page: 1, last_page: 1, per_page: 15, total: 0, unread_count: 0 },
			links: {},
		};
	});

	it("hides the badge at zero", () => {
		render(<NotificationBell />);
		expect(screen.queryByText("0")).not.toBeInTheDocument();
	});

	it("shows counts and caps them at 9+", () => {
		mockData = {
			data: [],
			meta: { current_page: 1, last_page: 1, per_page: 15, total: 10, unread_count: 10 },
			links: {},
		};
		render(<NotificationBell />);
		expect(screen.getByText("9+")).toBeInTheDocument();
	});

	it("lists messages after opening", () => {
		mockData = {
			data: [
				{
					id: "n1",
					type: "submitted",
					message: "Request submitted",
					request_id: 4,
					read_at: null,
					created_at: "2026-11-12T10:00:00Z",
				},
			],
			meta: { current_page: 1, last_page: 1, per_page: 15, total: 1, unread_count: 1 },
			links: {},
		};
		render(<NotificationBell />);
		fireEvent.click(screen.getByRole("button", { name: "Notifications" }));
		expect(screen.getByText("Request submitted")).toBeInTheDocument();
	});

	it("marks an item read and navigates to its request", () => {
		mockData = {
			data: [
				{
					id: "n1",
					type: "submitted",
					message: "Request submitted",
					request_id: 4,
					read_at: null,
					created_at: "2026-11-12T10:00:00Z",
				},
			],
			meta: { current_page: 1, last_page: 1, per_page: 15, total: 1, unread_count: 1 },
			links: {},
		};
		render(<NotificationBell />);
		fireEvent.click(screen.getByRole("button", { name: "Notifications" }));
		fireEvent.click(screen.getByText("Request submitted"));
		expect(mockRead).toHaveBeenCalledWith("n1");
		expect(mockPush).toHaveBeenCalledWith("/requests/4");
	});
});
