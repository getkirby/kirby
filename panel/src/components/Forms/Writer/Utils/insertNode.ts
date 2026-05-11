import {
	NodeType,
	type Attrs,
	type Fragment,
	type Mark,
	type Node
} from "prosemirror-model";
import type { Command } from "prosemirror-state";

export default function insertNode(
	type: NodeType,
	attrs?: Attrs | null,
	content?: Fragment | Node | Node[],
	marks?: Mark[]
): Command {
	return (state, dispatch): boolean => {
		const { tr } = state;
		const node = type.create(attrs, content, marks);

		tr.replaceSelectionWith(node).scrollIntoView();

		if (dispatch) {
			dispatch(tr);
		}

		return true;
	};
}
