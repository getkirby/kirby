import type { NodeSpec } from "prosemirror-model";
import type { Command } from "prosemirror-state";
import Node, { type NodeContext } from "../Node";

export default class BulletList extends Node {
	get button() {
		return {
			id: this.name,
			icon: "list-bullet",
			label: window.panel.t("toolbar.button.ul"),
			name: this.name,
			when: ["listItem", "bulletList", "orderedList", "paragraph"]
		};
	}

	commands({ type, schema, utils }: NodeContext) {
		return () => utils.toggleList(type, schema.nodes.listItem);
	}

	inputRules({ type, utils }: NodeContext) {
		return [utils.wrappingInputRule(/^\s*([-+*])\s$/, type)];
	}

	keys({ type, schema, utils }: NodeContext): Record<string, Command> {
		return {
			"Shift-Ctrl-8": utils.toggleList(type, schema.nodes.listItem)
		};
	}

	get name() {
		return "bulletList";
	}

	get schema(): NodeSpec {
		return {
			content: "listItem+",
			group: "block",
			parseDOM: [{ tag: "ul" }],
			toDOM: () => ["ul", 0]
		};
	}
}
