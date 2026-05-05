/**
 * Taken from: https://github.com/atlassian/prosemirror-utils
 */

import isNodeSelection from "./isNodeSelection";
import equalNodeType from "./equalNodeType";

export default function findSelectedNodeOfType(nodeType) {
	return (selection) => {
		if (isNodeSelection(selection) === true) {
			const { node, $from } = selection;

			if (equalNodeType(nodeType, node) === true) {
				return { node, pos: $from.pos, depth: $from.depth };
			}
		}
	};
}
