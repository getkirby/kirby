import { describe, expect, it } from "vitest";
import { createSchemaWithNodes } from "@test/unit/editor";
import equalNodeType from "./equalNodeType";

const schema = createSchemaWithNodes({
	heading: { group: "block", content: "inline*" }
});

const paragraphType = schema.nodes.paragraph;
const headingType = schema.nodes.heading;
const paragraphNode = schema.node(paragraphType);

describe("equalNodeType", () => {
	describe("single NodeType", () => {
		it("returns true when the node type matches", () => {
			expect(equalNodeType(paragraphType, paragraphNode)).toBe(true);
		});

		it("returns false when the node type does not match", () => {
			expect(equalNodeType(headingType, paragraphNode)).toBe(false);
		});
	});

	describe("array of NodeTypes", () => {
		it("returns true when the node type is in the array", () => {
			expect(equalNodeType([paragraphType, headingType], paragraphNode)).toBe(
				true
			);
		});

		it("returns false when the node type is not in the array", () => {
			expect(equalNodeType([headingType], paragraphNode)).toBe(false);
		});

		it("returns false for an empty array", () => {
			expect(equalNodeType([], paragraphNode)).toBe(false);
		});
	});
});
