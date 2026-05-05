import { describe, expect, it, vi } from "vitest";
import { Schema } from "prosemirror-model";
import { EditorState, Transaction } from "prosemirror-state";
import insertNode from "./insertNode";

const schema = new Schema({
	nodes: {
		doc: { content: "block+" },
		paragraph: { group: "block", content: "inline*" },
		image: {
			group: "block",
			attrs: { src: { default: "" } }
		},
		text: { group: "inline" }
	}
});

const paragraphType = schema.nodes.paragraph;
const imageType = schema.nodes.image;

function emptyState(): EditorState {
	return EditorState.create({
		doc: schema.node("doc", null, [schema.node("paragraph")])
	});
}

describe("insertNode", () => {
	it("returns true", () => {
		const state = emptyState();
		expect(insertNode(paragraphType)(state)).toBe(true);
		expect(insertNode(paragraphType)(state, vi.fn())).toBe(true);
	});

	it("calls dispatch with a transaction when provided", () => {
		const state = emptyState();
		const dispatch = vi.fn();
		insertNode(paragraphType)(state);
		expect(dispatch).not.toHaveBeenCalled();

		insertNode(paragraphType)(state, dispatch);
		expect(dispatch).toHaveBeenCalledExactlyOnceWith(expect.any(Transaction));
	});

	it("inserts the node with the given attrs", () => {
		const state = emptyState();
		let dispatched: Transaction | undefined;
		insertNode(imageType, { src: "photo.jpg" })(state, (tr: Transaction) => {
			dispatched = tr;
		});
		const imageNode = dispatched!.doc.firstChild;
		expect(imageNode?.type).toBe(imageType);
		expect(imageNode?.attrs.src).toBe("photo.jpg");
	});
});
