import { describe, expect, it } from "vitest";
import { type Node, Schema } from "prosemirror-model";
import { EditorState, TextSelection, type Transaction } from "prosemirror-state";
import toggleList from "./toggleList";

const schema = new Schema({
	nodes: {
		doc: { content: "block+" },
		bulletList: { group: "block", content: "listItem+" },
		orderedList: {
			group: "block",
			content: "listItem+",
			attrs: { order: { default: 1 } }
		},
		listItem: { content: "paragraph block*", defining: true },
		paragraph: { group: "block", content: "inline*" },
		text: { group: "inline" }
	}
});

const bulletListType = schema.nodes.bulletList;
const orderedListType = schema.nodes.orderedList;
const listItemType = schema.nodes.listItem;
const paragraphType = schema.nodes.paragraph;

// doc(paragraph("a"))
// Positions: 0=before paragraph, 1=inside paragraph, 2=after "a", 3=after paragraph
const docWithParagraph = schema.node("doc", null, [
	schema.node("paragraph", null, [schema.text("a")])
]);

// doc(bulletList(listItem(paragraph("a"))))
// Positions: 0=before bulletList, 1=before listItem, 2=before paragraph, 3=inside paragraph, 4=after "a"
const docWithBulletList = schema.node("doc", null, [
	schema.node("bulletList", null, [
		schema.node("listItem", null, [
			schema.node("paragraph", null, [schema.text("a")])
		])
	])
]);

// doc(orderedList(listItem(paragraph("a"))))
const docWithOrderedList = schema.node("doc", null, [
	schema.node("orderedList", null, [
		schema.node("listItem", null, [
			schema.node("paragraph", null, [schema.text("a")])
		])
	])
]);

function stateAt(doc: Node, pos: number): EditorState {
	return EditorState.create({ doc, selection: TextSelection.create(doc, pos) });
}

function applyCommand(
	state: EditorState,
	command: ReturnType<typeof toggleList>
): EditorState | null {
	let result: EditorState | null = null;
	command(state, (tr: Transaction) => {
		result = state.apply(tr);
	});
	return result;
}

describe("toggleList", () => {
	describe("when the cursor is not in a list", () => {
		it("returns true", () => {
			const state = stateAt(docWithParagraph, 1);
			expect(toggleList(bulletListType, listItemType)(state, undefined)).toBe(
				true
			);
		});

		it("wraps the block in the given list type", () => {
			const state = stateAt(docWithParagraph, 1);
			const next = applyCommand(
				state,
				toggleList(bulletListType, listItemType)
			);
			expect(next?.doc.firstChild?.type).toBe(bulletListType);
		});
	});

	describe("when the cursor is in a list of the same type", () => {
		it("returns true", () => {
			const state = stateAt(docWithBulletList, 3);
			expect(toggleList(bulletListType, listItemType)(state, undefined)).toBe(
				true
			);
		});

		it("lifts the list item out of the list", () => {
			const state = stateAt(docWithBulletList, 3);
			const next = applyCommand(
				state,
				toggleList(bulletListType, listItemType)
			);
			expect(next?.doc.firstChild?.type).toBe(paragraphType);
		});
	});

	describe("when the cursor is in a list of a different type", () => {
		it("returns true", () => {
			const state = stateAt(docWithBulletList, 3);
			const result = toggleList(orderedListType, listItemType)(state, () => {});
			expect(result).toBe(true);
		});

		it("changes the list type from bullet to ordered", () => {
			const state = stateAt(docWithBulletList, 3);
			const next = applyCommand(
				state,
				toggleList(orderedListType, listItemType)
			);
			expect(next?.doc.firstChild?.type).toBe(orderedListType);
		});

		it("changes the list type from ordered to bullet", () => {
			const state = stateAt(docWithOrderedList, 3);
			const next = applyCommand(
				state,
				toggleList(bulletListType, listItemType)
			);
			expect(next?.doc.firstChild?.type).toBe(bulletListType);
		});
	});
});
