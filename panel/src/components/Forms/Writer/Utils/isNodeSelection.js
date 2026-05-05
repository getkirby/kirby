import { NodeSelection } from "prosemirror-state";

/**
 * Taken from: https://github.com/atlassian/prosemirror-utils
 */
export default function isNodeSelection(selection) {
	return selection instanceof NodeSelection;
}
