import { describe, expect, it, vi } from "vitest";
import { Transaction } from "prosemirror-state";
import { createSchemaWithMarks, select } from "@test/unit/editor";
import removeMark from "./removeMark";

const schema = createSchemaWithMarks({ bold: {} });

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

describe("removeMark", () => {
	describe("collapsed selection", () => {
		it("removes the mark across its full range, not just at the cursor position", () => {
			const state = select(doc, 8);
			let dispatched: Transaction | undefined;
			expect(
				removeMark(boldType)(state, (tr: Transaction) => {
					dispatched = tr;
				})
			).toBe(true);
			expect(dispatched!.doc.rangeHasMark(7, 11, boldType)).toBe(false);
		});

		it("does not dispatch when no mark is found", () => {
			const dispatch = vi.fn();
			expect(removeMark(boldType)(select(doc, 3), dispatch)).toBe(false);
			expect(dispatch).not.toHaveBeenCalled();
		});
	});

	describe("non-collapsed selection", () => {
		it("removes the mark within the selection", () => {
			const state = select(doc, 7, 11);
			let dispatched: Transaction | undefined;
			expect(
				removeMark(boldType)(state, (tr: Transaction) => {
					dispatched = tr;
				})
			).toBe(true);
			expect(dispatched!.doc.rangeHasMark(7, 11, boldType)).toBe(false);
		});
	});

	describe("dispatch", () => {
		it("calls dispatch with a transaction", () => {
			const dispatch = vi.fn();

			removeMark(boldType)(select(doc, 8));
			expect(dispatch).not.toHaveBeenCalled();

			removeMark(boldType)(select(doc, 8), dispatch);
			expect(dispatch).toHaveBeenCalledExactlyOnceWith(expect.any(Transaction));
		});
	});
});
