import type { NodeSpec } from "prosemirror-model";
import Node, { type NodeContext } from "../Node";

export default class Paragraph extends Node {
	get button() {
		return {
			id: this.name,
			icon: "paragraph",
			label: window.panel.t("toolbar.button.paragraph"),
			name: this.name,
			separator: true
		};
	}

	commands({ utils, schema, type }: NodeContext) {
		return {
			paragraph: () => {
				const activeNodes = this.editor.activeNodes;

				if (activeNodes.includes("bulletList")) {
					return utils.toggleList(
						schema.nodes.bulletList,
						schema.nodes.listItem
					);
				}

				if (activeNodes.includes("orderedList")) {
					return utils.toggleList(
						schema.nodes.orderedList,
						schema.nodes.listItem
					);
				}

				if (activeNodes.includes("quote")) {
					return utils.toggleWrap(schema.nodes.quote);
				}

				return utils.setBlockType(type);
			}
		};
	}

	get name() {
		return "paragraph";
	}

	get schema(): NodeSpec {
		return {
			content: "inline*",
			group: "block",
			draggable: false,
			parseDOM: [{ tag: "p" }],
			toDOM: () => ["p", 0]
		};
	}
}
