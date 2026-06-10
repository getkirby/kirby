import type { InputRule } from "prosemirror-inputrules";
import type { NodeSpec } from "prosemirror-model";
import type { Command } from "prosemirror-state";
import type { ExtensionCommand } from "../Extension";
import Node, { type NodeContext } from "../Node";

export default class OrderedList extends Node {
	get button() {
		return {
			id: this.name,
			icon: "list-numbers",
			label: window.panel.t("toolbar.button.ol"),
			name: this.name,
			when: ["listItem", "bulletList", "orderedList", "paragraph"],
			separator: true
		};
	}

	commands({ type, schema, utils }: NodeContext): ExtensionCommand {
		return () => utils.toggleList(type, schema.nodes.listItem);
	}

	inputRules({ type, utils }: NodeContext): InputRule[] {
		return [
			utils.wrappingInputRule(
				/^(\d+)\.\s$/,
				type,
				(match) => ({ order: +match[1] }),
				(match, node) => node.childCount + node.attrs.order === +match[1]
			)
		];
	}

	keys({ type, schema, utils }: NodeContext): Record<string, Command> {
		return {
			"Shift-Ctrl-9": utils.toggleList(type, schema.nodes.listItem)
		};
	}

	get name() {
		return "orderedList";
	}

	get schema(): NodeSpec {
		return {
			attrs: {
				order: {
					default: 1
				}
			},
			content: "listItem+",
			group: "block",
			parseDOM: [
				{
					tag: "ol",
					getAttrs: (dom) => ({
						order: dom.hasAttribute("start") ? +dom.getAttribute("start")! : 1
					})
				}
			],
			toDOM: (node) =>
				node.attrs.order === 1
					? ["ol", 0]
					: ["ol", { start: node.attrs.order }, 0]
		};
	}
}
