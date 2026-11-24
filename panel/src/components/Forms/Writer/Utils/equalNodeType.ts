import { Node, NodeType } from "prosemirror-model";

/**
 * Checks if the type a given `node` equals to a given `nodeType`.
 *
 * Taken from: https://github.com/atlassian/prosemirror-utils
 */
export default function equalNodeType(
	nodeType: NodeType | NodeType[],
	node: Node
): boolean {
	if (Array.isArray(nodeType) && nodeType.includes(node.type)) {
		return true;
	}

	return node.type === nodeType;
}
