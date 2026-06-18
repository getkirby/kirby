import type { InputRule } from "prosemirror-inputrules";
import type { NodeSpec } from "prosemirror-model";
import type { Command } from "prosemirror-state";
import type { ExtensionCommand } from "../Extension";
import Node, { type NodeContext } from "../Node";

export default class Quote extends Node {
	get button() {
		return {
			id: this.name,
			icon: "quote",
			label: window.panel.t("field.blocks.quote.name"),
			name: this.name
		};
	}

	commands({ type, utils }: NodeContext): ExtensionCommand {
		return () => utils.toggleWrap(type);
	}

	inputRules({ type, utils }: NodeContext): InputRule[] {
		return [utils.wrappingInputRule(/^\s*>\s$/, type)];
	}

	keys({ utils }: NodeContext): Record<string, Command> {
		return {
			"Shift-Tab": (state, dispatch) => utils.lift(state, dispatch)
		};
	}

	get name() {
		return "quote";
	}

	get schema(): NodeSpec {
		return {
			content: "block+",
			group: "block",
			defining: true,
			draggable: false,
			parseDOM: [{ tag: "blockquote" }],
			toDOM: () => ["blockquote", 0]
		};
	}
}
