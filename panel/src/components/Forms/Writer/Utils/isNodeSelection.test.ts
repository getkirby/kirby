import { describe, expect, it } from "vitest";
import { Schema } from "prosemirror-model";
import { NodeSelection, TextSelection } from "prosemirror-state";
import isNodeSelection from "./isNodeSelection";

const schema = new Schema({
	nodes: {
		doc: { content: "block+" },
		paragraph: { group: "block", content: "inline*" },
		image: { group: "block" },
		text: { group: "inline" }
	}
});

const doc = schema.node("doc", null, [
	schema.node("paragraph"),
	schema.node("image")
]);

describe("isNodeSelection", () => {
	it("returns true for a NodeSelection", () => {
		expect(isNodeSelection(NodeSelection.create(doc, 2))).toBe(true);
	});

	it("returns false for a TextSelection", () => {
		expect(isNodeSelection(TextSelection.create(doc, 1))).toBe(false);
	});

	it("returns false for null", () => {
		expect(isNodeSelection(null)).toBe(false);
	});

	it("returns false for a plain object", () => {
		expect(isNodeSelection({})).toBe(false);
	});
});
