import { describe, expect, it } from "vitest";
import { Schema } from "prosemirror-model";
import { EditorState, TextSelection } from "prosemirror-state";
import markIsActive from "./markIsActive";

const schema = new Schema({
	nodes: {
		doc: { content: "block+" },
		paragraph: { group: "block", content: "inline*" },
		text: { group: "inline" }
	},
	marks: {
		bold: {}
	}
});

const boldType = schema.marks.bold;
const boldMark = schema.mark("bold");

// doc(paragraph("plain " + "bold"(bold) + " plain"))
// Positions: 0=before paragraph, 1-6="plain ", 7-10="bold", 11-16=" plain", 17=after paragraph
const doc = schema.node("doc", null, [
	schema.node("paragraph", null, [
		schema.text("plain "),
		schema.text("bold", [boldMark]),
		schema.text(" plain")
	])
]);

function stateAt(pos: number): EditorState {
	return EditorState.create({ doc, selection: TextSelection.create(doc, pos) });
}

function stateWithSelection(from: number, to: number): EditorState {
	return EditorState.create({
		doc,
		selection: TextSelection.create(doc, from, to)
	});
}

describe("markIsActive", () => {
	describe("collapsed selection", () => {
		it("returns true when the cursor is inside marked text", () => {
			expect(markIsActive(stateAt(8), boldType)).toBe(true);
		});

		it("returns false when the cursor is inside unmarked text", () => {
			expect(markIsActive(stateAt(3), boldType)).toBe(false);
		});

		it("returns true when stored marks contain the mark type", () => {
			const state = stateAt(3); // cursor in plain text
			const stateWithStoredMark = state.apply(state.tr.addStoredMark(boldMark));
			expect(markIsActive(stateWithStoredMark, boldType)).toBe(true);
		});
	});

	describe("non-collapsed selection", () => {
		it("returns true when the selection spans marked text", () => {
			expect(markIsActive(stateWithSelection(7, 11), boldType)).toBe(true);
		});

		it("returns false when the selection spans only unmarked text", () => {
			expect(markIsActive(stateWithSelection(1, 6), boldType)).toBe(false);
		});

		it("returns true when the selection partially overlaps marked text", () => {
			expect(markIsActive(stateWithSelection(5, 9), boldType)).toBe(true);
		});
	});
});
