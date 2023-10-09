import { describe, expect, it } from "vitest";
import { extension, name } from "./file";

describe.concurrent("$helper.file.extension()", () => {
	it("returns the extension of a filename", () => {
		expect(extension("file.txt")).toBe("txt");
		expect(extension("file.docx")).toBe("docx");
		expect(extension("file")).toBe("file");
	});
});

describe.concurrent("$helper.file.name()", () => {
	it("returns the name without extension of a filename", () => {
		expect(name("file.txt")).toBe("file");
		expect(name("file.docx")).toBe("file");
		expect(name("file")).toBe("");
	});
});
