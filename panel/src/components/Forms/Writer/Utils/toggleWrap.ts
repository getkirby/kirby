import { lift, wrapIn } from "prosemirror-commands";
import type { Attrs, NodeType } from "prosemirror-model";
import type { Command } from "prosemirror-state";
import nodeIsActive from "./nodeIsActive";

export default function toggleWrap(type: NodeType, attrs: Attrs = {}): Command {
	return (state, dispatch, view): boolean => {
		if (nodeIsActive(state, type, attrs) === true) {
			return lift(state, dispatch);
		}

		return wrapIn(type, attrs)(state, dispatch, view);
	};
}
