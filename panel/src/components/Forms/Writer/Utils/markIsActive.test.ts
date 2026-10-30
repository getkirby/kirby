import { describe, expect, it } from "vitest";
import { createSchemaWithMarks, select } from "@test/unit/editor";
import markIsActive from "./markIsActive";

const schema = createSchemaWithMarks({
	bold: {}
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

describe("markIsActive", () => {
	describe("collapsed selection", () => {
		it("returns true when the cursor is inside marked text", () => {
			expect(markIsActive(select(doc, 8), boldType)).toBe(true);
		});

		it("returns false when the cursor is inside unmarked text", () => {
			expect(markIsActive(select(doc, 3), boldType)).toBe(false);
		});

		it("returns true when stored marks contain the mark type", () => {
			const state = select(doc, 3); // cursor in plain text
			const stateWithStoredMark = state.apply(state.tr.addStoredMark(boldMark));
			expect(markIsActive(stateWithStoredMark, boldType)).toBe(true);
		});
	});

	describe("non-collapsed selection", () => {
		it("returns true when the selection spans marked text", () => {
			expect(markIsActive(select(doc, 7, 11), boldType)).toBe(true);
		});

		it("returns false when the selection spans only unmarked text", () => {
			expect(markIsActive(select(doc, 1, 6), boldType)).toBe(false);
		});

		it("returns true when the selection partially overlaps marked text", () => {
			expect(markIsActive(select(doc, 5, 9), boldType)).toBe(true);
		});
	});
});
