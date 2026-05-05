import { describe, expect, it } from "vitest";
import { type Node, Fragment, Schema, Slice } from "prosemirror-model";
import { Plugin } from "prosemirror-state";
import pasteRule from "./pasteRule";

const schema = new Schema({
	nodes: {
		doc: { content: "block+" },
		paragraph: { group: "block", content: "inline*" },
		text: { group: "inline" }
	},
	marks: {
		bold: {},
		link: { attrs: { href: { default: "" } } }
	}
});

const boldType = schema.marks.bold;
const linkType = schema.marks.link;

// Matches the literal word "hello" — the mark is applied to the full match, not a capture group
const regexp = () => /hello/g;

function applyRule(plugin: Plugin, slice: Slice): Slice {
	return (plugin.props as { transformPasted: (s: Slice) => Slice }).transformPasted(
		slice
	);
}

function paragraph(...children: Node[]): Slice {
	return new Slice(Fragment.from(schema.node("paragraph", null, children)), 0, 0);
}

describe("pasteRule", () => {
	it("applies the mark to the full match", () => {
		const plugin = pasteRule(regexp(), boldType);
		const result = applyRule(plugin, paragraph(schema.text("hello")));
		const para = result.content.firstChild!;
		expect(para.child(0).text).toBe("hello");
		expect(para.child(0).marks[0].type).toBe(boldType);
	});

	it("preserves text before and after a match", () => {
		const plugin = pasteRule(regexp(), boldType);
		const result = applyRule(plugin, paragraph(schema.text("say hello there")));
		const para = result.content.firstChild!;
		expect(para.child(0).text).toBe("say ");
		expect(para.child(1).text).toBe("hello");
		expect(para.child(1).marks[0].type).toBe(boldType);
		expect(para.child(2).text).toBe(" there");
	});

	it("handles multiple matches in a single text node", () => {
		const plugin = pasteRule(regexp(), boldType);
		const result = applyRule(plugin, paragraph(schema.text("hello and hello")));
		const para = result.content.firstChild!;
		expect(para.child(0).text).toBe("hello");
		expect(para.child(0).marks[0].type).toBe(boldType);
		expect(para.child(1).text).toBe(" and ");
		expect(para.child(2).text).toBe("hello");
		expect(para.child(2).marks[0].type).toBe(boldType);
	});

	it("passes through text with no match unchanged", () => {
		const plugin = pasteRule(regexp(), boldType);
		const result = applyRule(plugin, paragraph(schema.text("no match")));
		const para = result.content.firstChild!;
		expect(para.child(0).text).toBe("no match");
		expect(para.child(0).marks).toHaveLength(0);
	});

	it("computes attrs from a getAttrs function", () => {
		const plugin = pasteRule(regexp(), linkType, (match: RegExpMatchArray) => ({
			href: `https://${match}.com`
		}));
		const result = applyRule(plugin, paragraph(schema.text("hello")));
		const para = result.content.firstChild!;
		expect(para.child(0).marks[0].attrs.href).toBe("https://hello.com");
	});

	it("uses attrs from a getAttrs object", () => {
		const plugin = pasteRule(regexp(), linkType, { href: "https://static.com" });
		const result = applyRule(plugin, paragraph(schema.text("hello")));
		const para = result.content.firstChild!;
		expect(para.child(0).marks[0].attrs.href).toBe("https://static.com");
	});
});
