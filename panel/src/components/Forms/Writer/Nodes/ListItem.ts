import type { NodeSpec } from "prosemirror-model";
import type { Command } from "prosemirror-state";
import Node, { type NodeContext } from "../Node";

export default class ListItem extends Node {
	keys({ type, utils }: NodeContext): Record<string, Command> {
		return {
			Enter: utils.splitListItem(type),
			"Shift-Tab": utils.liftListItem(type),
			Tab: utils.sinkListItem(type)
		};
	}

	get name() {
		return "listItem";
	}

	get schema(): NodeSpec {
		return {
			content: "paragraph block*",
			defining: true,
			draggable: false,
			parseDOM: [{ tag: "li" }],
			toDOM: () => ["li", 0]
		};
	}
}
