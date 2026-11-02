import { describe, expect, it } from "vitest";
import { applyCommand, createSchemaWithNodes, select } from "@test/unit/editor";
import toggleBlockType from "./toggleBlockType";

const schema = createSchemaWithNodes({
	heading: {
		group: "block",
		content: "inline*",
		attrs: { level: { default: 1 } }
	}
});

const headingType = schema.nodes.heading;
const paragraphType = schema.nodes.paragraph;

// doc(paragraph("a"))
// Positions: 0=before paragraph, 1=inside paragraph, 2=after "a", 3=after paragraph
const docWithParagraph = schema.node("doc", null, [
	schema.node("paragraph", null, [schema.text("a")])
]);

// doc(heading({level:1}, "a"))
// Positions: 0=before heading, 1=inside heading, 2=after "a", 3=after heading
const docWithHeading = schema.node("doc", null, [
	schema.node("heading", { level: 1 }, [schema.text("a")])
]);

describe("toggleBlockType", () => {
	describe("when block type is not active", () => {
		it("returns true", () => {
			const state = select(docWithParagraph, 1);
			expect(
				toggleBlockType(headingType, paragraphType)(state, undefined)
			).toBe(true);
		});

		it("sets the block to the given type", () => {
			const state = select(docWithParagraph, 1);
			const next = applyCommand(
				state,
				toggleBlockType(headingType, paragraphType)
			);
			expect(next?.doc.firstChild?.type).toBe(headingType);
		});
	});

	describe("when block type is already active", () => {
		it("returns true", () => {
			const state = select(docWithHeading, 1);
			expect(
				toggleBlockType(headingType, paragraphType)(state, undefined)
			).toBe(true);
		});

		it("reverts the block to the toggle type", () => {
			const state = select(docWithHeading, 1);
			const next = applyCommand(
				state,
				toggleBlockType(headingType, paragraphType)
			);
			expect(next?.doc.firstChild?.type).toBe(paragraphType);
		});
	});

	describe("with attrs", () => {
		it("sets the block type when attrs do not match the active node", () => {
			const state = select(docWithHeading, 1);
			const next = applyCommand(
				state,
				toggleBlockType(headingType, paragraphType, { level: 2 })
			);
			expect(next?.doc.firstChild?.attrs.level).toBe(2);
		});

		it("reverts to the toggle type when attrs match the active node", () => {
			const state = select(docWithHeading, 1);
			const next = applyCommand(
				state,
				toggleBlockType(headingType, paragraphType, { level: 1 })
			);
			expect(next?.doc.firstChild?.type).toBe(paragraphType);
		});
	});
});
