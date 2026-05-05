import { describe, expect, it } from "vitest";
import { Schema } from "prosemirror-model";
import getMarkRange from "./getMarkRange";

const schema = new Schema({
	nodes: {
		doc: { content: "block+" },
		paragraph: { group: "block", content: "inline*" },
		text: { group: "inline" }
	},
	marks: {
		link: { attrs: { href: {} } },
		bold: {}
	}
});

const linkType = schema.marks.link;
const boldType = schema.marks.bold;
const linkMark = schema.mark("link", { href: "https://example.com" });

// doc(paragraph("plain "(no mark) + "linked"(link) + " plain"(no mark)))
// Positions: 0=before paragraph, 1-6="plain ", 7-12="linked", 13-18=" plain", 19=after paragraph
const doc = schema.node("doc", null, [
	schema.node("paragraph", null, [
		schema.text("plain "),
		schema.text("linked", [linkMark]),
		schema.text(" plain")
	])
]);

// doc(paragraph("a"(link+bold) + "b"(link) + "c"(link+bold)))
// ProseMirror keeps these separate since mark sets differ
// Positions: 0=before paragraph, 1="a", 2="b", 3="c", 4=after paragraph
const docExpand = schema.node("doc", null, [
	schema.node("paragraph", null, [
		schema.text("a", [linkMark, schema.mark("bold")]),
		schema.text("b", [linkMark]),
		schema.text("c", [linkMark, schema.mark("bold")])
	])
]);

describe("getMarkRange", () => {
	it("returns false when $pos is null", () => {
		expect(getMarkRange(null, linkType)).toBe(false);
	});

	it("returns false when type is null", () => {
		const $pos = doc.resolve(8);
		expect(getMarkRange($pos, null)).toBe(false);
	});

	it("returns false when the node at the position has no matching mark", () => {
		const $pos = doc.resolve(3); // inside "plain "
		expect(getMarkRange($pos, linkType)).toBe(false);
	});

	it("returns false when the position is at the end of the parent", () => {
		const $pos = doc.resolve(19); // after last char, before closing paragraph token
		expect(getMarkRange($pos, linkType)).toBe(false);
	});

	it("returns the range of the marked node", () => {
		const $pos = doc.resolve(9); // inside "linked"
		expect(getMarkRange($pos, linkType)).toEqual({ from: 7, to: 13 });
	});

	it("returns false when the mark type does not match", () => {
		const $pos = doc.resolve(9); // inside "linked" which only has link, not bold
		expect(getMarkRange($pos, boldType)).toBe(false);
	});

	it("expands the range to include adjacent nodes with the same mark", () => {
		const $pos = docExpand.resolve(2); // inside "b" which has only link
		expect(getMarkRange($pos, linkType)).toEqual({ from: 1, to: 4 });
	});
});
