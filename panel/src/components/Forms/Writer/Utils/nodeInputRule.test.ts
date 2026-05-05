import { describe, expect, it } from "vitest";
import { Schema } from "prosemirror-model";
import { EditorState, type Transaction } from "prosemirror-state";
import { InputRule } from "prosemirror-inputrules";
import nodeInputRule from "./nodeInputRule";

type InputRuleHandler = (
	state: EditorState,
	match: RegExpMatchArray,
	start: number,
	end: number
) => Transaction | null;

function getHandler(rule: InputRule): InputRuleHandler {
	return (rule as unknown as { handler: InputRuleHandler }).handler;
}

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

// doc(paragraph("x"))
// Positions: 0=before paragraph, 1=inside paragraph, 2=after "x", 3=after paragraph
const doc = schema.node("doc", null, [
	schema.node("paragraph", null, [schema.text("x")])
]);

const state = EditorState.create({ doc });

function makeMatch(...groups: string[]): RegExpMatchArray {
	return Object.assign(groups, {
		index: 0,
		input: groups[0]
	}) as RegExpMatchArray;
}

describe("nodeInputRule", () => {
	describe("when match is found", () => {
		it("returns a transaction", () => {
			const rule = nodeInputRule(/^#\s/, headingType, {});
			expect(getHandler(rule)(state, makeMatch("# "), 0, 3)).not.toBeNull();
		});

		it("replaces the matched range with the given node type", () => {
			const rule = nodeInputRule(/^#\s/, headingType, {});
			const tr = getHandler(rule)(state, makeMatch("# "), 0, 3)!;
			expect(state.apply(tr).doc.firstChild!.type).toBe(headingType);
		});

		it("applies attrs from a getAttrs object", () => {
			const rule = nodeInputRule(/^#\s/, headingType, { level: 2 });
			const tr = getHandler(rule)(state, makeMatch("# "), 0, 3)!;
			expect(state.apply(tr).doc.firstChild!.attrs.level).toBe(2);
		});

		it("applies attrs from a getAttrs function", () => {
			const rule = nodeInputRule(/^(#{1,6})\s/, headingType, (match: RegExpMatchArray) => ({
				level: match[1].length
			}));
			const tr = getHandler(rule)(state, makeMatch("## ", "##"), 0, 3)!;
			expect(state.apply(tr).doc.firstChild!.attrs.level).toBe(2);
		});
	});

	describe("when no match is found", () => {
		it("returns null", () => {
			const rule = nodeInputRule(/^#\s/, headingType, {});
			expect(getHandler(rule)(state, makeMatch(""), 0, 3)).toBeNull();
		});
	});
});
