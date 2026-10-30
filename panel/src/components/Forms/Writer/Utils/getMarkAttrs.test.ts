import { describe, expect, it } from "vitest";
import { createSchemaWithMarks, select } from "@test/unit/editor";
import getMarkAttrs from "./getMarkAttrs";

const schema = createSchemaWithMarks({
	bold: {},
	link: { attrs: { href: {} } }
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

describe("getMarkAttrs", () => {
	it("returns the attrs of the matching mark within the selection", () => {
		const state = select(doc, 7, 12);
		expect(getMarkAttrs(state, linkType)).toEqual({
			href: "https://example.com"
		});
	});

	it("returns an empty object when the mark is not within the selection", () => {
		const state = select(doc, 1, 6);
		expect(getMarkAttrs(state, linkType)).toEqual({});
	});

	it("returns an empty object when the mark type has no attrs", () => {
		const state = select(doc, 1, 6);
		expect(getMarkAttrs(state, boldType)).toEqual({});
	});

	it("returns the first matching mark when the selection spans multiple marked nodes", () => {
		const state = select(doc, 1, 12);
		expect(getMarkAttrs(state, linkType)).toEqual({
			href: "https://example.com"
		});
	});
});
