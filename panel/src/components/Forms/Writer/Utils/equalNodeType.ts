import { Node, NodeType } from "prosemirror-model";

/**
 * Taken from: https://github.com/atlassian/prosemirror-utils
 *
 * Checks if the type a given `node` equals to a given `nodeType`.
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
