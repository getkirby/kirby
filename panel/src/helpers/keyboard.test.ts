import { afterEach, describe, expect, it, vi } from "vitest";
import { metaKey } from "./keyboard";

describe("$helper.keyboard", () => {
	describe("metaKey()", () => {
		afterEach(() => {
			vi.restoreAllMocks();
		});

		it("should return cmd on macOS", () => {
			vi.spyOn(window.navigator, "userAgent", "get").mockReturnValue(
				"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)"
			);
			expect(metaKey()).toBe("cmd");
		});

		it("should return ctrl on other systems", () => {
			vi.spyOn(window.navigator, "userAgent", "get").mockReturnValue(
				"Mozilla/5.0 (Windows NT 10.0; Win64; x64)"
			);
			expect(metaKey()).toBe("ctrl");
		});
	});
});
