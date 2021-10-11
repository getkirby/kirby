/**
 * Taken from: https://github.com/atlassian/prosemirror-utils
 */

import isNodeSelection from "./isNodeSelection";
import equalNodeType from "./equalNodeType";

export default (nodeType) => (selection) => {
  if (isNodeSelection(selection)) {
    const { node, $from } = selection;
    if (equalNodeType(nodeType, node)) {
      return { node, pos: $from.pos, depth: $from.depth };
    }
  }
};
