import getMarkRange from "./getMarkRange";

export default function (type, attrs) {
	return (state, dispatch) => {
		const { tr, selection, doc } = state;

		const { ranges, empty } = selection;

		if (empty) {
			const range = getMarkRange(selection.$from, type);

			if (range === false) {
				return false;
			}

			const { from, to } = range;

			if (doc.rangeHasMark(from, to, type)) {
				tr.removeMark(from, to, type);
			}

			tr.addMark(from, to, type.create(attrs));
		} else {
			ranges.forEach((ref$1) => {
				const { $to, $from } = ref$1;

				if (doc.rangeHasMark($from.pos, $to.pos, type)) {
					tr.removeMark($from.pos, $to.pos, type);
				}

				tr.addMark($from.pos, $to.pos, type.create(attrs));
			});
		}

		if (dispatch) {
			dispatch(tr);
		}

		return true;
	};
}
