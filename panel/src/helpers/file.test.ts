import { afterEach, describe, expect, it } from "vitest";
import { extension, name, niceSize } from "./file";

describe("$helper.file.extension()", () => {
	it("returns the extension of a filename", () => {
		expect(extension("file.txt")).toBe("txt");
		expect(extension("file.docx")).toBe("docx");
		expect(extension("file")).toBe("file");
	});
});

describe("$helper.file.name()", () => {
	it("returns the name without extension of a filename", () => {
		expect(name("file.txt")).toBe("file");
		expect(name("file.docx")).toBe("file");
		expect(name("file")).toBe("");
	});
});

describe("$helper.file.niceSize()", () => {
	afterEach(() => {
		document.documentElement.lang = "";
	});

	// same as `F::niceSize()`
	it.each([
		[0, "0\u00a0KB"],
		[512, "512\u00a0B"],
		[1024, "1\u00a0KB"],
		[1536000, "1.46\u00a0MB"],
		[1572864, "1.5\u00a0MB"],
		[2684354560, "2.5\u00a0GB"]
	])("formats %i bytes", (size, expected) => {
		expect(niceSize(size)).toBe(expected);
	});

	it("formats the number for the interface language", () => {
		document.documentElement.lang = "de";
		expect(niceSize(1536000)).toBe("1,46\u00a0MB");
	});
});
