import { describe, expect, it } from "vitest";
import { Schema } from "prosemirror-model";
import { NodeSelection, TextSelection } from "prosemirror-state";
import findSelectedNodeOfType from "./findSelectedNodeOfType";

const schema = new Schema({
	nodes: {
		doc: { content: "block+" },
		paragraph: { group: "block", content: "inline*" },
		image: { group: "block", inline: false },
		text: { group: "inline" }
	}
});

// doc(paragraph(), image())
// Positions: 0=before paragraph, 1=after paragraph (empty), 2=before image, 3=after image
const doc = schema.node("doc", null, [
	schema.node("paragraph"),
	schema.node("image")
]);

const imageType = schema.nodes.image;
const paragraphType = schema.nodes.paragraph;

describe("findSelectedNodeOfType", () => {
	it("returns the node when it is selected and matches the type", () => {
		const selection = NodeSelection.create(doc, 2);
		const result = findSelectedNodeOfType(imageType)(selection);
		expect(result?.node.type).toBe(imageType);
		expect(result?.pos).toBe(2);
	});

	it("returns undefined when the selected node does not match the type", () => {
		const selection = NodeSelection.create(doc, 2);
		expect(findSelectedNodeOfType(paragraphType)(selection)).toBeUndefined();
	});

	it("returns undefined when the selection is not a NodeSelection", () => {
		const selection = TextSelection.create(doc, 1);
		expect(findSelectedNodeOfType(imageType)(selection)).toBeUndefined();
	});
});
