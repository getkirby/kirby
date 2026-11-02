import { describe, expect, it } from "vitest";
import type { Node } from "prosemirror-model";
import { TextSelection } from "prosemirror-state";
import { createSchemaWithNodes } from "@test/unit/editor";
import findParentNode from "./findParentNode";

const schema = createSchemaWithNodes({});

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
