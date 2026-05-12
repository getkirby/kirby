import type { Attrs, MarkType } from "prosemirror-model";
import type { Command } from "prosemirror-state";
import getMarkRange from "./getMarkRange";

export default function updateMark(type: MarkType, attrs: Attrs): Command {
	return (state, dispatch): boolean => {
		const { tr, selection } = state;

		const { ranges, empty } = selection;

		if (empty) {
			const range = getMarkRange(selection.$from, type);

			if (range === false) {
				return false;
			}

			const { from, to } = range;

			tr.removeMark(from, to, type);
			tr.addMark(from, to, type.create(attrs));
		} else {
			ranges.forEach(({ $to, $from }) => {
				tr.removeMark($from.pos, $to.pos, type);
				tr.addMark($from.pos, $to.pos, type.create(attrs));
			});
		}

		if (dispatch) {
			dispatch(tr);
		}

		return true;
	};
}
