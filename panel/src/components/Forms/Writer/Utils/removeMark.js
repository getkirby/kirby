import getMarkRange from "./getMarkRange";

export default function removeMark(type) {
	return (state, dispatch) => {
		const { tr, selection } = state;
		let { from, to } = selection;
		const { $from, empty } = selection;

		if (empty) {
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
