import { describe, expect, it } from "vitest";
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
	it("formats bytes", () => {
		expect(niceSize(0)).toBe("0B");
		expect(niceSize(1024)).toBe("1KB");
		expect(niceSize(1048576)).toBe("1MB");
	});
});
