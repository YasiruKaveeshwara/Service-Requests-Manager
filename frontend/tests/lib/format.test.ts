import { describe, expect, it } from "vitest";

import { formatDateTime, humanize } from "../../lib/format";

describe("format helpers", () => {
	it("humanizes in_progress", () => {
		expect(humanize("in_progress")).toBe("In progress");
	});

	it("formats null as a dash", () => {
		expect(formatDateTime(null)).toBe("-");
	});

	it("formats an ISO date", () => {
		expect(formatDateTime("2026-11-11T15:04:00Z")).toBeTruthy();
	});
});
