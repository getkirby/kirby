import type { NodeSpec, NodeType } from "prosemirror-model";
import type { Command } from "prosemirror-state";
import type { Utils } from "../Utils";
import Node, { type NodeContext } from "../Node";

export default class HardBreak extends Node<{
	enter: boolean;
}> {
	commands({ utils, type }: NodeContext) {
		return () => this.createHardBreak(utils, type);
	}

	private createHardBreak(utils: Utils, type: NodeType): Command {
		return utils.chainCommands(utils.exitCode, utils.insertNode(type));
	}

	get defaults() {
		return {
			enter: false
		};
	}

	keys({ utils, type }: NodeContext): Record<string, Command> {
		const command = this.createHardBreak(utils, type);

		const keymap: Record<string, Command> = {
			"Mod-Enter": command,
			"Shift-Enter": command
		};

		if (this.options.enter === true) {
			keymap["Enter"] = command;
		}

		return keymap;
	}

	get name() {
		return "hardBreak";
	}

	get schema(): NodeSpec {
		return {
			inline: true,
			group: "inline",
			selectable: false,
			parseDOM: [{ tag: "br" }],
			toDOM: () => ["br"]
		};
	}
}
