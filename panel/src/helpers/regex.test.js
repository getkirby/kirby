import { describe, expect, it } from "vitest";
import "./regex";

describe.concurrent("RegExp.escape()", () => {
	it("should escape special characters in a regex string", () => {
		expect(RegExp.escape("hello.world")).toBe("hello\\.world");
		expect(RegExp.escape("a*b+c?d|e(f)g[h]i{j}k")).toBe(
			"a\\*b\\+c\\?d|e\\(f\\)g\\[h\\]i\\{j\\}k"
		);
		expect(RegExp.escape("^$\\.*+?()[]{}|-")).toBe(
			"\\^\\$\\\\\\.\\*\\+\\?\\(\\)\\[\\]\\{\\}|\\-"
		);
	});
});
