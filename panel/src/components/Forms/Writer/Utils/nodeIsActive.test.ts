import { describe, expect, it } from "vitest";
import { Schema } from "prosemirror-model";
import { EditorState, NodeSelection, TextSelection } from "prosemirror-state";
import nodeIsActive from "./nodeIsActive";

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
const paragraphType = schema.nodes.paragraph;

// doc(heading({level:1}, "a"), paragraph("b"))
// Positions: 0=before heading, 1=inside heading, 2=after "a", 3=before paragraph, 4=inside paragraph, 5=after "b"
const doc = schema.node("doc", null, [
	schema.node("heading", { level: 1 }, [schema.text("a")]),
	schema.node("paragraph", null, [schema.text("b")])
]);

function stateAt(pos: number): EditorState {
	return EditorState.create({ doc, selection: TextSelection.create(doc, pos) });
}

describe("nodeIsActive", () => {
	it("returns false when no matching node contains the cursor", () => {
		expect(nodeIsActive(stateAt(4), headingType)).toBe(false);
	});

	it("returns true when the cursor is inside a node of the given type", () => {
		expect(nodeIsActive(stateAt(1), headingType)).toBe(true);
	});

	it("returns true when a node of the given type is selected via NodeSelection", () => {
		const state = EditorState.create({
			doc,
			selection: NodeSelection.create(doc, 0)
		});
		expect(nodeIsActive(state, headingType)).toBe(true);
	});

	it("returns false when a different node type is selected", () => {
		const state = EditorState.create({
			doc,
			selection: NodeSelection.create(doc, 0)
		});
		expect(nodeIsActive(state, paragraphType)).toBe(false);
	});

	describe("with attrs", () => {
		it("returns true when the node attrs match", () => {
			expect(nodeIsActive(stateAt(1), headingType, { level: 1 })).toBe(true);
		});

		it("returns false when the node attrs do not match", () => {
			expect(nodeIsActive(stateAt(1), headingType, { level: 2 })).toBe(false);
		});
	});
});
