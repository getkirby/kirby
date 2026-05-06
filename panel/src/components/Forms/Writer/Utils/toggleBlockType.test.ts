import { describe, expect, it } from "vitest";
import { Schema } from "prosemirror-model";
import { EditorState, TextSelection, type Transaction } from "prosemirror-state";
import toggleBlockType from "./toggleBlockType";

const schema = new Schema({
	nodes: {
		doc: { content: "block+" },
		heading: {
			group: "block",
			content: "inline*",
			attrs: { level: { default: 1 } }
		},
		paragraph: { group: "block", content: "inline*" },
		text: { group: "inline" }
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

function stateAt(doc: typeof docWithParagraph, pos: number): EditorState {
	return EditorState.create({ doc, selection: TextSelection.create(doc, pos) });
}

function applyCommand(
	state: EditorState,
	command: ReturnType<typeof toggleBlockType>
): EditorState | null {
	let result: EditorState | null = null;
	command(state, (tr: Transaction) => {
		result = state.apply(tr);
	});
	return result;
}

describe("toggleBlockType", () => {
	describe("when block type is not active", () => {
		it("returns true", () => {
			const state = stateAt(docWithParagraph, 1);
			expect(
				toggleBlockType(headingType, paragraphType)(state, undefined)
			).toBe(true);
		});

		it("sets the block to the given type", () => {
			const state = stateAt(docWithParagraph, 1);
			const next = applyCommand(
				state,
				toggleBlockType(headingType, paragraphType)
			);
			expect(next?.doc.firstChild?.type).toBe(headingType);
		});
	});

	describe("when block type is already active", () => {
		it("returns true", () => {
			const state = stateAt(docWithHeading, 1);
			expect(
				toggleBlockType(headingType, paragraphType)(state, undefined)
			).toBe(true);
		});

		it("reverts the block to the toggle type", () => {
			const state = stateAt(docWithHeading, 1);
			const next = applyCommand(
				state,
				toggleBlockType(headingType, paragraphType)
			);
			expect(next?.doc.firstChild?.type).toBe(paragraphType);
		});
	});

	describe("with attrs", () => {
		it("sets the block type when attrs do not match the active node", () => {
			const state = stateAt(docWithHeading, 1);
			const next = applyCommand(
				state,
				toggleBlockType(headingType, paragraphType, { level: 2 })
			);
			expect(next?.doc.firstChild?.attrs.level).toBe(2);
		});

		it("reverts to the toggle type when attrs match the active node", () => {
			const state = stateAt(docWithHeading, 1);
			const next = applyCommand(
				state,
				toggleBlockType(headingType, paragraphType, { level: 1 })
			);
			expect(next?.doc.firstChild?.type).toBe(paragraphType);
		});
	});
});
