import { afterEach, describe, expect, it, vi } from "vitest";

import { dismiss, subscribe, toast } from "../../lib/toast";

describe("toast store", () => {
	afterEach(() => {
		vi.useRealTimers();
	});

	it("notifies subscribers and dismisses an error", () => {
		vi.useFakeTimers();
		const listener = vi.fn();
		const unsubscribe = subscribe(listener);

		toast.error("Something went wrong.");
		const items = listener.mock.lastCall?.[0] ?? [];
		expect(items).toHaveLength(1);
		expect(items[0]).toMatchObject({ kind: "error", message: "Something went wrong." });

		dismiss(items[0].id);
		expect(listener.mock.lastCall?.[0]).toHaveLength(0);
		unsubscribe();
	});
});
