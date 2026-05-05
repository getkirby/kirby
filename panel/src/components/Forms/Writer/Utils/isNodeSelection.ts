import { NodeSelection } from "prosemirror-state";

/**
 * Taken from: https://github.com/atlassian/prosemirror-utils
 */
export default function isNodeSelection(
	selection: unknown
): selection is NodeSelection {
	return selection instanceof NodeSelection;
}
