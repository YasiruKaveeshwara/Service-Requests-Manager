import { act, renderHook } from "@testing-library/react";
import { describe, expect, it, vi } from "vitest";

import { useDebounce } from "@/hooks/useDebounce";

describe("useDebounce", () => {
	it("returns the latest value after the delay", () => {
		vi.useFakeTimers();
		const { result, rerender } = renderHook(({ value }) => useDebounce(value, 300), {
			initialProps: { value: "first" },
		});
		rerender({ value: "latest" });
		expect(result.current).toBe("first");
		act(() => vi.advanceTimersByTime(300));
		expect(result.current).toBe("latest");
		vi.useRealTimers();
	});
});
