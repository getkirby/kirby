/**
 * Taken from: https://github.com/atlassian/prosemirror-utils
 *
 * :: ($pos: ResolvedPos, predicate: (node: ProseMirrorNode) → boolean) → ?{pos: number, start: number, depth: number, node: ProseMirrorNode}
 * Iterates over parent nodes starting from the given `$pos`, returning the closest node and its start position `predicate` returns truthy for. `start` points to the start position of the node, `pos` points directly before the node.
 *
 * ```javascript
 * const predicate = node => node.type === schema.nodes.blockquote;
 * const parent = findParentNodeClosestToPos(state.doc.resolve(5), predicate);
 * ```
 */
export default ($pos, predicate) => {
  for (let i = $pos.depth; i > 0; i--) {
    const node = $pos.node(i);
    if (predicate(node)) {
      return {
        pos: i > 0 ? $pos.before(i) : 0,
        start: $pos.start(i),
        depth: i,
        node
      };
    }
  }
};
