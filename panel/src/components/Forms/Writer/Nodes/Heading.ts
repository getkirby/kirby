import type { InputRule } from "prosemirror-inputrules";
import type { NodeSpec } from "prosemirror-model";
import type { Command } from "prosemirror-state";
import type { Button, ExtensionCommand } from "../Extension";
import Node, { type NodeContext } from "../Node";

export default class Heading extends Node<{ levels: number[] }> {
	get button() {
		const buttons: Button[] = this.options.levels.map((level) => ({
			id: `h${level}`,
			command: `h${level}`,
			icon: `h${level}`,
			label: window.panel.t("toolbar.button.heading." + level),
			attrs: { level },
			name: this.name,
			when: ["heading", "paragraph"]
		}));

		buttons[buttons.length - 1].separator = true;

		return buttons;
	}

	commands({
		type,
		schema,
		utils
	}: NodeContext): Record<string, ExtensionCommand> {
		const commands: Record<string, ExtensionCommand> = {
			toggleHeading: (attrs) =>
				utils.toggleBlockType(type, schema.nodes.paragraph, attrs)
		};

		for (const level of this.options.levels) {
			commands[`h${level}`] = () =>
				utils.toggleBlockType(type, schema.nodes.paragraph, { level });
		}

		return commands;
	}

	get defaults() {
		return {
			levels: [1, 2, 3, 4, 5, 6]
		};
	}

	inputRules({ type, utils }: NodeContext): InputRule[] {
		return this.options.levels.map((level) =>
			utils.textblockTypeInputRule(
				new RegExp(`^#{${level}}\\s$`),
				type,
				() => ({ level })
			)
		);
	}

	keys({ type, utils }: NodeContext): Record<string, Command> {
		return this.options.levels.reduce(
			(items, level) => ({
				...items,
				[`Shift-Ctrl-${level}`]: utils.setBlockType(type, { level })
			}),
			{} as Record<string, Command>
		);
	}

	get name() {
		return "heading";
	}

	get schema(): NodeSpec {
		return {
			attrs: {
				level: {
					default: 1
				}
			},
			content: "inline*",
			group: "block",
			defining: true,
			draggable: false,
			parseDOM: this.options.levels.map((level) => ({
				tag: `h${level}`,
				attrs: { level }
			})),
			toDOM: (node) => [`h${node.attrs.level}`, 0]
		};
	}
}
