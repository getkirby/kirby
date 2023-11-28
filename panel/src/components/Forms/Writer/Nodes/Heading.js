import Node from "../Node";

export default class Heading extends Node {
	get button() {
		const buttons = this.options.levels.map((level) => ({
			id: `h${level}`,
			command: `h${level}`,
			icon: `h${level}`,
			label: window.panel.$t("toolbar.button.heading." + level),
			attrs: { level },
			name: this.name,
			when: ["heading", "paragraph"]
		}));

		// last button has separator line afterwards
		buttons[buttons.length - 1].separator = true;

		return buttons;
	}

	commands({ type, schema, utils }) {
		let commands = {
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

	inputRules({ type, utils }) {
		return this.options.levels.map((level) =>
			utils.textblockTypeInputRule(
				new RegExp(`^(#{1,${level}})\\s$`),
				type,
				() => ({ level })
			)
		);
	}

	keys({ type, utils }) {
		return this.options.levels.reduce(
			(items, level) => ({
				...items,
				...{
					[`Shift-Ctrl-${level}`]: utils.setBlockType(type, { level })
				}
			}),
			{}
		);
	}

	get name() {
		return "heading";
	}

	get schema() {
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
