/**
 * Taken from: https://github.com/atlassian/prosemirror-utils
 *
 * Iterates over parent nodes starting from the given `$pos`, returning the
 * closest node and its start position `predicate` returns truthy for. `start`
 * points to the start position of the node, `pos` points directly before the
 * node.
 *
 * @example
 * const predicate = node => node.type === schema.nodes.blockquote;
 * const parent = findParentNodeClosestToPos(state.doc.resolve(5), predicate);
 */
export default function findParentNodeClosestToPos($pos, predicate) {
	for (let i = $pos.depth; i > 0; i--) {
		const node = $pos.node(i);

		if (predicate(node) === true) {
			return {
				pos: i > 0 ? $pos.before(i) : 0,
				start: $pos.start(i),
				depth: i,
				node
			};
		}
	}
}
