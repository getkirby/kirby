import Node from "../Node";

export default class Quote extends Node {
	get button() {
		return {
			id: this.name,
			icon: "quote",
			label: window.panel.$t("field.blocks.quote.name"),
			name: this.name
		};
	}

	commands({ type, utils }) {
		return () => utils.toggleWrap(type);
	}

	inputRules({ type, utils }) {
		return [utils.wrappingInputRule(/^\s*>\s$/, type)];
	}

	keys({ utils }) {
		return {
			"Shift-Tab": (state, dispatch) => utils.lift(state, dispatch)
		};
	}

	get name() {
		return "quote";
	}

	get schema() {
		return {
			content: "block+",
			group: "block",
			defining: true,
			draggable: false,
			parseDOM: [
				{
					tag: "blockquote"
				}
			],
			toDOM: () => ["blockquote", 0]
		};
	}
}
