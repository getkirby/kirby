import { describe, expect, it } from "vitest";
import { Schema } from "prosemirror-model";
import { EditorState, Transaction } from "prosemirror-state";
import markInputRule from "./markInputRule";

const schema = new Schema({
	nodes: {
		doc: { content: "block+" },
		paragraph: { group: "block", content: "inline*" },
		text: { group: "inline" }
	},
	marks: {
		code: {},
		bold: {},
		strike: { excludes: "bold" } // mutually exclusive with bold
	}
});

const codeType = schema.marks.code;
const boldType = schema.marks.bold;

/**
 * Simulates firing an InputRule
 *
 * The handler is called BEFORE the last typed character
 * is inserted, so the document is always one character shorter
 * than the full match string.
 */
function fire(
	rule: ReturnType<typeof markInputRule>,
	state: EditorState,
	matchStrings: string[],
	start: number,
	end: number
): Transaction | null {
	const match = Object.assign(matchStrings, {
		index: 0,
		input: matchStrings[0]
	}) as RegExpMatchArray;
	return (rule as unknown as { handler: (...args: unknown[]) => Transaction | null }).handler(
		state,
		match,
		start,
		end
	);
}

// Shared document: paragraph containing "`text" (no closing backtick).
// The closing ` is the last typed character — not yet in the document.
// Positions inside the paragraph: 1=before `, 2-5=text, 6=end of content.
const codeDoc = schema.node("doc", null, [
	schema.node("paragraph", null, [schema.text("`text")])
]);

describe("markInputRule", () => {
	it("single-char delimiter", () => {
		// /`([^`]+)`$/ → match[0]="`text`", match[1]="text"
		const rule = markInputRule(/`([^`]+)`$/, codeType);
		const tr = fire(
			rule,
			EditorState.create({ doc: codeDoc }),
			["`text`", "text"],
			1,
			6
		);
		expect(tr!.doc.textBetween(1, tr!.doc.content.size - 1, "")).toBe("text");
		expect(tr!.doc.nodeAt(1)?.marks.some((m) => m.type === codeType)).toBe(
			true
		);
	});

	it("two-char delimiter", () => {
		// /(\*\*(([^*]+))\*\*)$/ → match[0]="**word**", match[1]="**word**", match[2]="word"
		// The last typed * is not in the doc — document contains "**word*" (7 chars).
		// Positions: 1=before first *, 8=end of content.
		const rule = markInputRule(/(\*\*(([^*]+))\*\*)$/, boldType);
		const doc = schema.node("doc", null, [
			schema.node("paragraph", null, [schema.text("**word*")])
		]);
		const tr = fire(
			rule,
			EditorState.create({ doc }),
			["**word**", "**word**", "word"],
			1,
			8
		);
		expect(tr!.doc.textBetween(1, tr!.doc.content.size - 1, "")).toBe("word");
		expect(tr!.doc.nodeAt(1)?.marks.some((m) => m.type === boldType)).toBe(
			true
		);
	});

	it("calls a getAttrs function with the match array", () => {
		let receivedMatch: RegExpMatchArray | null = null;
		const rule = markInputRule(/`([^`]+)`$/, codeType, (match: RegExpMatchArray) => {
			receivedMatch = match;
			return {};
		});
		fire(rule, EditorState.create({ doc: codeDoc }), ["`text`", "text"], 1, 6);
		expect(receivedMatch![0]).toBe("`text`");
	});

	it("returns null when a mutually exclusive mark occupies the range", () => {
		// strike excludes bold — a bold input rule must not fire over struck text
		const rule = markInputRule(/`([^`]+)`$/, boldType);
		const doc = schema.node("doc", null, [
			schema.node("paragraph", null, [
				schema.text("`text", [schema.mark("strike")])
			])
		]);
		const tr = fire(
			rule,
			EditorState.create({ doc }),
			["`text`", "text"],
			1,
			6
		);
		expect(tr).toBeNull();
	});
});
