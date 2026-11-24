import { describe, expect, it } from "vitest";
import Doc from "./Doc";

const doc = new Doc();

describe("Doc", () => {
	describe("name", () => {
		it("returns 'doc'", () => {
			expect(doc.name).toBe("doc");
		});
	});

	describe("schema", () => {
		it("uses block content by default", () => {
			expect(doc.schema).toStrictEqual({ content: "block+" });
		});

		it("uses inline content when the inline option is true", () => {
			const doc = new Doc({ inline: true });
			expect(doc.schema).toStrictEqual({ content: "inline*" });
		});
	});

	describe("type", () => {
		it("returns 'node'", () => {
			expect(doc.type).toBe("node");
		});
	});
});
