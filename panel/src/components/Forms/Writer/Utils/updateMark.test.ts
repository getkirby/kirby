import { describe, expect, it } from "vitest";
import { Schema } from "prosemirror-model";
import { EditorState, TextSelection, type Transaction } from "prosemirror-state";
import updateMark from "./updateMark";

const schema = new Schema({
	nodes: {
		doc: { content: "block+" },
		paragraph: { group: "block", content: "inline*" },
		text: { group: "inline" }
	},
	marks: {
		link: {
			attrs: { href: { default: "" } },
			inclusive: false
		}
	}
});

const linkType = schema.marks.link;

// doc(paragraph("a" + "bc"(link{href:"old"}) + "d"))
// Positions: 0=before paragraph, 1=before "a", 2=before "b" (start of link),
//            3=between "b"/"c", 4=after "c" (end of link), 5=after "d"
const doc = schema.node("doc", null, [
	schema.node("paragraph", null, [
		schema.text("a"),
		schema.text("bc", [schema.mark("link", { href: "old" })]),
		schema.text("d")
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

function applyCommand(
	state: EditorState,
	command: ReturnType<typeof updateMark>
): EditorState {
	let next: EditorState | null = null;
	command(state, (tr: Transaction) => {
		next = state.apply(tr);
	});
	return next!;
}

describe("updateMark", () => {
	describe("collapsed selection", () => {
		it("returns false when the cursor is not inside a marked range", () => {
			const state = stateAt(1); // inside "a" — no link mark
			expect(updateMark(linkType, { href: "new" })(state, undefined)).toBe(
				false
			);
		});

		it("returns true when the cursor is inside a marked range", () => {
			const state = stateAt(3); // inside "bc" — has link mark
			expect(updateMark(linkType, { href: "new" })(state, undefined)).toBe(
				true
			);
		});

		it("updates the mark attrs across the full mark range", () => {
			const state = stateAt(3);
			const next = applyCommand(state, updateMark(linkType, { href: "new" }));
			const para = next.doc.firstChild!;
			expect(para.child(1).marks[0].attrs.href).toBe("new");
		});

		it("does not affect text outside the mark range", () => {
			const state = stateAt(3);
			const next = applyCommand(state, updateMark(linkType, { href: "new" }));
			const para = next.doc.firstChild!;
			expect(para.child(0).marks).toHaveLength(0); // "a" unchanged
			expect(para.child(2).marks).toHaveLength(0); // "d" unchanged
		});
	});

	describe("non-empty selection", () => {
		it("returns true", () => {
			const state = stateWithSelection(2, 4); // selecting "bc"
			expect(updateMark(linkType, { href: "new" })(state, undefined)).toBe(
				true
			);
		});

		it("updates the mark attrs within the selection", () => {
			const state = stateWithSelection(2, 4); // selecting "bc"
			const next = applyCommand(state, updateMark(linkType, { href: "new" }));
			const para = next.doc.firstChild!;
			expect(para.child(1).marks[0].attrs.href).toBe("new");
		});

		it("adds the mark to unmarked text within the selection", () => {
			const state = stateWithSelection(1, 2); // selecting "a"
			const next = applyCommand(state, updateMark(linkType, { href: "new" }));
			const para = next.doc.firstChild!;
			expect(para.child(0).marks[0].attrs.href).toBe("new");
		});
	});
});
