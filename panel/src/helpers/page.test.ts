import { describe, expect, it } from "vitest";
import { status } from "./page";

// mock $t() function
// @ts-expect-error - window.panel has no type yet
window.panel = {
	$t: (value: string) => value
};

describe.concurrent("$helper.page.status()", () => {
	it("returns correct props for draft", () => {
		const result = status("draft");
		expect(result.icon).toBe("status-draft");
		expect(result.theme).toBe("negative-icon");
		expect(result.disabled).toBe(false);
		expect(result.size).toBe("xs");
		expect(result.style).toBe("--icon-size: 15px");
	});

	it("returns correct props for unlisted", () => {
		const result = status("unlisted");
		expect(result.icon).toBe("status-unlisted");
		expect(result.theme).toBe("info-icon");
		expect(result.disabled).toBe(false);
		expect(result.size).toBe("xs");
		expect(result.style).toBe("--icon-size: 15px");
	});

	it("returns correct props for listed", () => {
		const result = status("listed");
		expect(result.icon).toBe("status-listed");
		expect(result.theme).toBe("positive-icon");
		expect(result.disabled).toBe(false);
		expect(result.size).toBe("xs");
		expect(result.style).toBe("--icon-size: 15px");
	});

	it("returns correct title", () => {
		expect(status("draft").title).toBe("page.status: page.status.draft");
	});

	it("appends disabled label when disabled", () => {
		const result = status("draft", true);
		expect(result.disabled).toBe(true);
		expect(result.title).toBe("page.status: page.status.draft (disabled)");
	});
});
