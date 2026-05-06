import { describe, expect, it } from "vitest";
import { type Node, Schema } from "prosemirror-model";
import findParentNodeClosestToPos from "./findParentNodeClosestToPos";

const schema = new Schema({
	nodes: {
		doc: { content: "block+" },
		blockquote: { group: "block", content: "block+" },
		paragraph: { group: "block", content: "inline*" },
		text: { group: "inline" }
	}
});

// doc(blockquote(paragraph("hello")))
// Positions: 0=before blockquote, 1=inside blockquote, 2=inside paragraph, 3-7=text, 8=after paragraph, 9=after blockquote
const doc = schema.node("doc", null, [
	schema.node("blockquote", null, [
		schema.node("paragraph", null, [schema.text("hello")])
	])
]);

describe("findParentNodeClosestToPos", () => {
	it("returns undefined when no parent matches", () => {
		const $pos = doc.resolve(4);
		expect(findParentNodeClosestToPos($pos, () => false)).toBeUndefined();
	});

	it("returns the closest matching parent", () => {
		const $pos = doc.resolve(4);
		const result = findParentNodeClosestToPos(
			$pos,
			(node: Node) => node.type === schema.nodes.paragraph
		);
		expect(result?.node.type).toBe(schema.nodes.paragraph);
		expect(result?.depth).toBe(2);
		expect(result?.pos).toBe(1); // position before paragraph
		expect(result?.start).toBe(2); // position inside paragraph
	});

	it("returns a more distant ancestor when the closer one does not match", () => {
		const $pos = doc.resolve(4);
		const result = findParentNodeClosestToPos(
			$pos,
			(node: Node) => node.type === schema.nodes.blockquote
		);
		expect(result?.node.type).toBe(schema.nodes.blockquote);
		expect(result?.depth).toBe(1);
		expect(result?.pos).toBe(0); // position before blockquote
		expect(result?.start).toBe(1); // position inside blockquote
	});

	it("returns the closest match when multiple ancestors match", () => {
		const $pos = doc.resolve(4);
		const result = findParentNodeClosestToPos($pos, () => true);
		expect(result?.node.type).toBe(schema.nodes.paragraph);
		expect(result?.depth).toBe(2);
	});

	it("does not match the doc node itself", () => {
		const $pos = doc.resolve(4);
		const result = findParentNodeClosestToPos(
			$pos,
			(node: Node) => node.type === schema.nodes.doc
		);
		expect(result).toBeUndefined();
	});
});
