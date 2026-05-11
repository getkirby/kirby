import type { MarkType } from "prosemirror-model";
import type { Command } from "prosemirror-state";
import getMarkRange from "./getMarkRange";

export default function removeMark(type: MarkType): Command {
	return (state, dispatch): boolean => {
		const { tr, selection } = state;
		let { from, to } = selection;
		const { $from, empty } = selection;

		if (empty === true) {
			const range = getMarkRange($from, type);

			if (range === false) {
				return false;
			}

			from = range.from;
			to = range.to;
		}

		tr.removeMark(from, to, type);

		if (dispatch) {
			dispatch(tr);
		}

		return true;
	};
}
