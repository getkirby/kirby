import { describe, expect, it } from "vitest";
import { type Node, Fragment, Schema, Slice } from "prosemirror-model";
import { Plugin } from "prosemirror-state";
import markPasteRule from "./markPasteRule";

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

// Matches <capture> and captures the inner text as match[1]
// A fresh instance per test is required to avoid shared lastIndex state
const regexp = () => /<(.+?)>/g;

function applyRule(plugin: Plugin, slice: Slice): Slice {
	return (
		plugin.props as { transformPasted: (s: Slice) => Slice }
	).transformPasted(slice);
}

function paragraph(...children: Node[]): Slice {
	return new Slice(
		Fragment.from(schema.node("paragraph", null, children)),
		0,
		0
	);
}

describe("markPasteRule", () => {
	it("applies the mark to the captured group of a match", () => {
		const plugin = markPasteRule(regexp(), boldType);
		const result = applyRule(plugin, paragraph(schema.text("<hello>")));
		const para = result.content.firstChild!;
		expect(para.child(0).text).toBe("hello");
		expect(para.child(0).marks[0].type).toBe(boldType);
	});

	it("preserves text before and after a match", () => {
		const plugin = markPasteRule(regexp(), boldType);
		const result = applyRule(
			plugin,
			paragraph(schema.text("before <hello> after"))
		);
		const para = result.content.firstChild!;
		expect(para.child(0).text).toBe("before ");
		expect(para.child(1).text).toBe("hello");
		expect(para.child(1).marks[0].type).toBe(boldType);
		expect(para.child(2).text).toBe(" after");
	});

	it("handles multiple matches in a single text node", () => {
		const plugin = markPasteRule(regexp(), boldType);
		const result = applyRule(plugin, paragraph(schema.text("<foo> and <bar>")));
		const para = result.content.firstChild!;
		expect(para.child(0).text).toBe("foo");
		expect(para.child(0).marks[0].type).toBe(boldType);
		expect(para.child(1).text).toBe(" and ");
		expect(para.child(2).text).toBe("bar");
		expect(para.child(2).marks[0].type).toBe(boldType);
	});

	it("passes through text with no match unchanged", () => {
		const plugin = markPasteRule(regexp(), boldType);
		const result = applyRule(plugin, paragraph(schema.text("no match here")));
		const para = result.content.firstChild!;
		expect(para.child(0).text).toBe("no match here");
		expect(para.child(0).marks).toHaveLength(0);
	});

	it("skips text nodes that already have a link mark", () => {
		const plugin = markPasteRule(regexp(), boldType);
		const result = applyRule(
			plugin,
			paragraph(
				schema.text("<hello>", [
					schema.mark("link", { href: "https://example.com" })
				])
			)
		);
		const para = result.content.firstChild!;
		expect(para.child(0).text).toBe("<hello>");
		expect(para.child(0).marks.some((m) => m.type === boldType)).toBe(false);
	});

	it("computes attrs from a getAttrs function", () => {
		const plugin = markPasteRule(regexp(), linkType, (match: RegExpMatchArray) => ({
			href: `https://${match[1]}.com`
		}));
		const result = applyRule(plugin, paragraph(schema.text("<example>")));
		const para = result.content.firstChild!;
		expect(para.child(0).marks[0].attrs.href).toBe("https://example.com");
	});

	it("uses attrs from a getAttrs object", () => {
		const plugin = markPasteRule(regexp(), linkType, {
			href: "https://static.com"
		});
		const result = applyRule(plugin, paragraph(schema.text("<anything>")));
		const para = result.content.firstChild!;
		expect(para.child(0).marks[0].attrs.href).toBe("https://static.com");
	});
});
