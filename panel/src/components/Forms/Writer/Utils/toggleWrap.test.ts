import { describe, expect, it } from "vitest";
import { type Node, Schema } from "prosemirror-model";
import { EditorState, TextSelection, type Transaction } from "prosemirror-state";
import toggleWrap from "./toggleWrap";

const schema = new Schema({
	nodes: {
		doc: { content: "block+" },
		blockquote: { group: "block", content: "block+" },
		paragraph: { group: "block", content: "inline*" },
		text: { group: "inline" }
	}
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

function stateAt(doc: Node, pos: number): EditorState {
	return EditorState.create({ doc, selection: TextSelection.create(doc, pos) });
}

function applyCommand(
	state: EditorState,
	command: ReturnType<typeof toggleWrap>
): EditorState | null {
	let result: EditorState | null = null;
	command(state, (tr: Transaction) => {
		result = state.apply(tr);
	});
	return result;
}

describe("toggleWrap", () => {
	describe("when the node type is not active", () => {
		it("returns true", () => {
			const state = stateAt(docWithParagraph, 1);
			expect(toggleWrap(blockquoteType)(state, undefined)).toBe(true);
		});

		it("wraps the selection in the given type", () => {
			const state = stateAt(docWithParagraph, 1);
			const next = applyCommand(state, toggleWrap(blockquoteType));
			expect(next?.doc.firstChild?.type).toBe(blockquoteType);
		});
	});

	describe("when the node type is already active", () => {
		it("returns true", () => {
			const state = stateAt(docWithBlockquote, 2);
			expect(toggleWrap(blockquoteType)(state, undefined)).toBe(true);
		});

		it("lifts the content out of the wrapper", () => {
			const state = stateAt(docWithBlockquote, 2);
			const next = applyCommand(state, toggleWrap(blockquoteType));
			expect(next?.doc.firstChild?.type).toBe(paragraphType);
		});
	});
});
