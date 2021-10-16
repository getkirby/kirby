/**
 * Taken from: https://github.com/atlassian/prosemirror-utils
 */
import { NodeSelection } from "prosemirror-state";

export default (selection) => {
  return selection instanceof NodeSelection;
};
