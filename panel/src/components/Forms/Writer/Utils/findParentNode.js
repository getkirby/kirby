import findParentNodeClosestToPos from "./findParentNodeClosestToPos";

/**
 * Taken from: https://github.com/atlassian/prosemirror-utils
 *
 * Iterates over parent nodes, returning the closest node and its start
 * position `predicate` returns truthy for. `start` points to the start
 * position of the node, `pos` points directly before the node.
 *
 * @example
 * const predicate = node => node.type === schema.nodes.blockquote;
 * const parent = findParentNode(predicate)(selection);
 */
export default function findParentNode(predicate) {
	return ({ $from }) => findParentNodeClosestToPos($from, predicate);
}
