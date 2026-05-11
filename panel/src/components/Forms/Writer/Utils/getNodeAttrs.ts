import { type Attrs, Node, NodeType } from "prosemirror-model";
import { EditorState } from "prosemirror-state";

export default function getNodeAttrs(
	state: EditorState,
	type: NodeType
): Attrs {
	const { from, to } = state.selection;
	const nodes: Node[] = [];

	state.doc.nodesBetween(from, to, (node) => {
		nodes.push(node);
	});

	const node = nodes.findLast((nodeItem) => nodeItem.type === type);

	if (node) {
		return node.attrs;
	}

	return {};
}
