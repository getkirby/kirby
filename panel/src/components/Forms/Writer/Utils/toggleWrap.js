import { lift, wrapIn } from "prosemirror-commands";
import nodeIsActive from "./nodeIsActive";

export default function toggleWrap(type, attrs = {}) {
	return (state, dispatch, view) => {
		const isActive = nodeIsActive(state, type, attrs);

		if (isActive) {
			return lift(state, dispatch);
		}

		return wrapIn(type, attrs)(state, dispatch, view);
	};
}
