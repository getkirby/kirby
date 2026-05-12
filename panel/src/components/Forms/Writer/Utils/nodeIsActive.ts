import { type Attrs, type Node, type NodeType } from "prosemirror-model";
import type { EditorState } from "prosemirror-state";
import { length } from "@/helpers/object";

import findParentNode from "./findParentNode";
import findSelectedNodeOfType from "./findSelectedNodeOfType";

export default function nodeIsActive(
	state: EditorState,
	type: NodeType,
	attrs: Attrs = {}
): boolean {
	const predicate = (node: Node) => node.type === type;
	const node =
		findSelectedNodeOfType(type)(state.selection) ??
		findParentNode(predicate)(state.selection);

	if (length(attrs) === 0 || !node) {
		return !!node;
	}

	return node.node.hasMarkup(type, { ...node.node.attrs, ...attrs });
}
