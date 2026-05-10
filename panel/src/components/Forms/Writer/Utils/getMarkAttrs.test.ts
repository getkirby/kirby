import { describe, expect, it } from "vitest";
import { Schema } from "prosemirror-model";
import { EditorState, TextSelection } from "prosemirror-state";
import getMarkAttrs from "./getMarkAttrs";

const schema = new Schema({
	nodes: {
		doc: { content: "block+" },
		paragraph: { group: "block", content: "inline*" },
		text: { group: "inline" }
	},
	marks: {
		bold: {},
		link: { attrs: { href: {} } }
	}
});

const boldType = schema.marks.bold;
const linkType = schema.marks.link;

// doc(paragraph("hello"(bold) " " "world"(link{href})))
// Positions: 0=before paragraph, 1-5="hello", 6=" ", 7-11="world", 12=after paragraph
const doc = schema.node("doc", null, [
	schema.node("paragraph", null, [
		schema.text("hello", [schema.mark("bold")]),
		schema.text(" "),
		schema.text("world", [schema.mark("link", { href: "https://example.com" })])
	])
]);

function stateWithSelection(from: number, to: number): EditorState {
	return EditorState.create({
		doc,
		selection: TextSelection.create(doc, from, to)
	});
}

describe("getMarkAttrs", () => {
	it("returns the attrs of the matching mark within the selection", () => {
		const state = stateWithSelection(7, 12);
		expect(getMarkAttrs(state, linkType)).toEqual({
			href: "https://example.com"
		});
	});

	it("returns an empty object when the mark is not within the selection", () => {
		const state = stateWithSelection(1, 6);
		expect(getMarkAttrs(state, linkType)).toEqual({});
	});

	it("returns an empty object when the mark type has no attrs", () => {
		const state = stateWithSelection(1, 6);
		expect(getMarkAttrs(state, boldType)).toEqual({});
	});

	it("returns the first matching mark when the selection spans multiple marked nodes", () => {
		const state = stateWithSelection(1, 12);
		expect(getMarkAttrs(state, linkType)).toEqual({
			href: "https://example.com"
		});
	});
});
