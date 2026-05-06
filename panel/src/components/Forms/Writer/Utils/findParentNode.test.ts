import { describe, expect, it } from "vitest";
import { type Node, Schema } from "prosemirror-model";
import { TextSelection } from "prosemirror-state";
import findParentNode from "./findParentNode";

const schema = new Schema({
	nodes: {
		doc: { content: "block+" },
		paragraph: { group: "block", content: "inline*" },
		text: { group: "inline" }
	}
});

// doc(paragraph("hello"))
// Positions: 0=before paragraph, 1=inside paragraph, 2-6=text, 7=after paragraph
const doc = schema.node("doc", null, [
	schema.node("paragraph", null, [schema.text("hello")])
]);

describe("findParentNode", () => {
	it("accepts a Selection and delegates to findParentNodeClosestToPos via $from", () => {
		const selection = TextSelection.create(doc, 3);
		const result = findParentNode(
			(node: Node) => node.type === schema.nodes.paragraph
		)(selection);
		expect(result?.node.type).toBe(schema.nodes.paragraph);
	});
});
