import { setBlockType } from "prosemirror-commands";
import type { Attrs, NodeType } from "prosemirror-model";
import type { Command } from "prosemirror-state";
import nodeIsActive from "./nodeIsActive";

export default function toggleBlockType(
	type: NodeType,
	toggleType: NodeType,
	attrs: Attrs = {}
): Command {
	return (state, dispatch, view): boolean => {
		if (nodeIsActive(state, type, attrs) === true) {
			return setBlockType(toggleType)(state, dispatch, view);
		}

		return setBlockType(type, attrs)(state, dispatch, view);
	};
}
