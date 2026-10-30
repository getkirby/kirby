import { describe, expect, it } from "vitest";
import { applyCommand, createSchemaWithNodes, select } from "@test/unit/editor";
import toggleWrap from "./toggleWrap";

const schema = createSchemaWithNodes({
	blockquote: { group: "block", content: "block+" }
});

const blockquoteType = schema.nodes.blockquote;
const paragraphType = schema.nodes.paragraph;

// doc(paragraph("a"))
// Positions: 0=before paragraph, 1=inside paragraph, 2=after "a", 3=after paragraph
const docWithParagraph = schema.node("doc", null, [
	schema.node("paragraph", null, [schema.text("a")])
]);

// doc(blockquote(paragraph("a")))
// Positions: 0=before blockquote, 1=inside blockquote, 2=inside paragraph, 3=after "a"
const docWithBlockquote = schema.node("doc", null, [
	schema.node("blockquote", null, [
		schema.node("paragraph", null, [schema.text("a")])
	])
]);

describe("toggleWrap", () => {
	describe("when the node type is not active", () => {
		it("returns true", () => {
			const state = select(docWithParagraph, 1);
			expect(toggleWrap(blockquoteType)(state, undefined)).toBe(true);
		});

		it("wraps the selection in the given type", () => {
			const state = select(docWithParagraph, 1);
			const next = applyCommand(state, toggleWrap(blockquoteType));
			expect(next?.doc.firstChild?.type).toBe(blockquoteType);
		});
	});

	describe("when the node type is already active", () => {
		it("returns true", () => {
			const state = select(docWithBlockquote, 2);
			expect(toggleWrap(blockquoteType)(state, undefined)).toBe(true);
		});

		it("lifts the content out of the wrapper", () => {
			const state = select(docWithBlockquote, 2);
			const next = applyCommand(state, toggleWrap(blockquoteType));
			expect(next?.doc.firstChild?.type).toBe(paragraphType);
		});
	});
});
