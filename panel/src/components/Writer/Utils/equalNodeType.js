/**
 * Taken from: https://github.com/atlassian/prosemirror-utils
 *
 * (nodeType: union<NodeType, [NodeType]>) → boolean
 * Checks if the type a given `node` equals to a given `nodeType`.
 */
export default (nodeType, node) => {
  return (
    (Array.isArray(nodeType) && nodeType.indexOf(node.type) > -1) ||
    node.type === nodeType
  );
};
