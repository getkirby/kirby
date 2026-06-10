import Mark from "../Mark";

export default class Bold extends Mark {
	get button() {
		return {
			icon: "bold",
			label: window.panel.t("toolbar.button.bold")
		};
	}

	commands() {
		return () => this.toggle();
	}

	inputRules({ type, utils }) {
		return [
			utils.markInputRule(/(?:^|\s)(\*\*([^*\s](?:(?:[^*]|\*(?![*\s]))*[^*\s])?)\*\*)$/, type),
			utils.markInputRule(/(?:^|\s)(__([^_\s](?:(?:[^_]|_(?![_\s]))*[^_\s])?)__)$/, type)
		];
	}

	keys() {
		return {
			"Mod-b": () => this.toggle()
		};
	}

	get name() {
		return "bold";
	}

	pasteRules({ type, utils }) {
		return [
			utils.markPasteRule(/(?<!\S)\*\*([^*\s](?:(?:[^*]|\*(?![*\s]))*[^*\s])?)\*\*(?!\S)/g, type),
			utils.markPasteRule(/(?<!\S)__([^_\s](?:(?:[^_]|_(?![_\s]))*[^_\s])?)__(?!\S)/g, type)
		];
	}

	get schema() {
		return {
			parseDOM: [
				{
					tag: "strong"
				},
				{
					tag: "b",
					getAttrs: (node) => node.style.fontWeight !== "normal" && null
				},
				{
					style: "font-weight",
					getAttrs: (value) => /^(bold(er)?|[5-9]\d{2,})$/.test(value) && null
				}
			],
			toDOM: () => ["strong", 0]
		};
	}
}
