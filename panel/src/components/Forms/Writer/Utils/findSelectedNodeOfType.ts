import type { Node, NodeType } from "prosemirror-model";
import { Selection } from "prosemirror-state";
import isNodeSelection from "./isNodeSelection";
import equalNodeType from "./equalNodeType";

/**
 * Taken from: https://github.com/atlassian/prosemirror-utils
 */
export default function findSelectedNodeOfType(
	nodeType: NodeType
): (
	selection: Selection
) => { node: Node; pos: number; depth: number } | undefined {
	return (selection: Selection) => {
		if (isNodeSelection(selection) === true) {
			const { node, $from } = selection;

			if (equalNodeType(nodeType, node) === true) {
				return { node, pos: $from.pos, depth: $from.depth };
			}
		}
	};
}
