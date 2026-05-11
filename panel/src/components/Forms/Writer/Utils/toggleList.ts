import type { Node, NodeType, Schema } from "prosemirror-model";
import { wrapInList, liftListItem } from "prosemirror-schema-list";
import type { Command } from "prosemirror-state";
import findParentNode from "./findParentNode";

function isList(node: Node, schema: Schema): boolean {
	return (
		node.type === schema.nodes.bulletList ||
		node.type === schema.nodes.orderedList ||
		node.type === schema.nodes.todoList
	);
}

export default function toggleList(
	listType: NodeType,
	itemType: NodeType
): Command {
	return (state, dispatch, view): boolean => {
		const { schema, selection } = state;
		const { $from, $to } = selection;
		const range = $from.blockRange($to);

		if (range === null) {
			return false;
		}

		const parentList = findParentNode((node) => isList(node, schema))(
			selection
		);

		if (range.depth >= 1 && parentList && range.depth - parentList.depth <= 1) {
			if (parentList.node.type === listType) {
				return liftListItem(itemType)(state, dispatch, view);
			}

			if (listType.validContent(parentList.node.content) === true) {
				const { tr } = state;
				tr.setNodeMarkup(parentList.pos, listType);

				if (dispatch) {
					dispatch(tr);
				}

				return true;
			}
		}

		return wrapInList(listType)(state, dispatch, view);
	};
}
