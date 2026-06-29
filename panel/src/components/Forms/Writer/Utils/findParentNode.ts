import { type Node } from "prosemirror-model";
import { Selection } from "prosemirror-state";
import findParentNodeClosestToPos from "./findParentNodeClosestToPos";

/**
 * Iterates over parent nodes, returning the closest node and its start
 * position `predicate` returns truthy for. `start` points to the start
 * position of the node, `pos` points directly before the node.
 *
 * @example
 * const predicate = node => node.type === schema.nodes.blockquote;
 * const parent = findParentNode(predicate)(selection);
 *
 * Taken from: https://github.com/atlassian/prosemirror-utils
 */
export default function findParentNode(
	predicate: (node: Node) => boolean
): (
	selection: Selection
) => { pos: number; start: number; depth: number; node: Node } | undefined {
	return ({ $from }: Selection) => findParentNodeClosestToPos($from, predicate);
}
