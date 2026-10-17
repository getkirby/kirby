import { afterEach, describe, expect, it } from "vitest";
import { format } from "./number";

describe("$helper.number.format()", () => {
	afterEach(() => {
		document.documentElement.lang = "";
	});

	it("formats in English without an interface language", () => {
		expect(format(1234.5)).toBe("1,234.5");
	});

	it("formats in the interface language", () => {
		document.documentElement.lang = "de";
		expect(format(1234.5)).toBe("1.234,5");
	});

	it("passes the options on", () => {
		expect(format(1.5, { minimumFractionDigits: 2 })).toBe("1.50");
	});
});
