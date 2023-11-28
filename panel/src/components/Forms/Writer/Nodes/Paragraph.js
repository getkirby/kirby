import Node from "../Node";

export default class Paragraph extends Node {
	get button() {
		return {
			id: this.name,
			icon: "paragraph",
			label: window.panel.$t("toolbar.button.paragraph"),
			name: this.name,
			separator: true
		};
	}

	commands({ utils, schema, type }) {
		return {
			paragraph: () => {
				if (this.editor.activeNodes.includes("bulletList")) {
					return utils.toggleList(
						schema.nodes.bulletList,
						schema.nodes.listItem
					);
				}

				if (this.editor.activeNodes.includes("orderedList")) {
					return utils.toggleList(
						schema.nodes.orderedList,
						schema.nodes.listItem
					);
				}

				if (this.editor.activeNodes.includes("quote")) {
					return utils.toggleWrap(schema.nodes.quote);
				}

				return utils.setBlockType(type);
			}
		};
	}

	get schema() {
		return {
			content: "inline*",
			group: "block",
			draggable: false,
			parseDOM: [
				{
					tag: "p"
				}
			],
			toDOM: () => ["p", 0]
		};
	}

	get name() {
		return "paragraph";
	}
}
