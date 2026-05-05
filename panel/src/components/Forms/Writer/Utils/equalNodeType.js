/**
 * Taken from: https://github.com/atlassian/prosemirror-utils
 *
 * Checks if the type a given `node` equals to a given `nodeType`.
 */
export default function equalNodeType(nodeType, node) {
	if (Array.isArray(nodeType) && nodeType.includes(node.type)) {
		return true;
	}

	return node.type === nodeType;
}
