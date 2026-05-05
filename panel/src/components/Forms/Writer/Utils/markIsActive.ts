import type { MarkType } from "prosemirror-model";
import type { EditorState } from "prosemirror-state";

export default function markIsActive(
	state: EditorState,
	type: MarkType
): boolean {
	const { from, $from, to, empty } = state.selection;

	if (empty === true) {
		return !!type.isInSet(state.storedMarks ?? $from.marks());
	}

	return state.doc.rangeHasMark(from, to, type);
}
