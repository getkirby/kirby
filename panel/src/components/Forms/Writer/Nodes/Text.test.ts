import { describe, expect, it } from "vitest";
import Text from "./Text";

const node = new Text();

describe("Text", () => {
	describe("name", () => {
		it("returns 'text'", () => {
			expect(node.name).toBe("text");
		});
	});

	describe("schema", () => {
		it("belongs to the inline group", () => {
			expect(node.schema).toStrictEqual({ group: "inline" });
		});
	});

	describe("type", () => {
		it("returns 'node'", () => {
			expect(node.type).toBe("node");
		});
	});
});
