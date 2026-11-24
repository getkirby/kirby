import { describe, expect, it } from "vitest";
import { EditorState, NodeSelection } from "prosemirror-state";
import { createSchemaWithNodes, select } from "@test/unit/editor";
import nodeIsActive from "./nodeIsActive";

const schema = createSchemaWithNodes({
	heading: {
		group: "block",
		content: "inline*",
		attrs: { level: { default: 1 } }
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

describe("nodeIsActive", () => {
	it("returns false when no matching node contains the cursor", () => {
		expect(nodeIsActive(select(doc, 4), headingType)).toBe(false);
	});

	it("returns true when the cursor is inside a node of the given type", () => {
		expect(nodeIsActive(select(doc, 1), headingType)).toBe(true);
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
			expect(nodeIsActive(select(doc, 1), headingType, { level: 1 })).toBe(
				true
			);
		});

		it("returns false when the node attrs do not match", () => {
			expect(nodeIsActive(select(doc, 1), headingType, { level: 2 })).toBe(
				false
			);
		});
	});
});
