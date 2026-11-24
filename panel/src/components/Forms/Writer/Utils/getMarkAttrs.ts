import { type Attrs, Mark, MarkType } from "prosemirror-model";
import { EditorState } from "prosemirror-state";

/**
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
export default function getMarkAttrs(
	state: EditorState,
	type: MarkType
): Attrs {
	const { from, to } = state.selection;
	const marks: Mark[] = [];

	state.doc.nodesBetween(from, to, (node) => {
		marks.push(...node.marks);
	});

	const mark = marks.find((markItem) => markItem.type === type);

	if (mark) {
		return mark.attrs;
	}

	return {};
}
