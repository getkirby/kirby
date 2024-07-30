import Mark from "../Mark";

export default class Italic extends Mark {
	get button() {
		return {
			icon: "italic",
			label: window.panel.$t("toolbar.button.italic")
		};
	}

	commands() {
		return () => this.toggle();
	}

	inputRules({ type, utils }) {
		return [
			utils.markInputRule(/(?:^|\s)(\*(?!\s+\*)((?:[^*]+))\*(?!\s+\*))$/, type),
			utils.markInputRule(/(?:^|\s)(_(?!\s+_)((?:[^_]+))_(?!\s+_))$/, type)
		];
	}

	keys() {
		return {
			"Mod-i": () => this.toggle()
		};
	}

	get name() {
		return "italic";
	}

	pasteRules({ type, utils }) {
		return [
			utils.markPasteRule(/(?:^|\s)(\*(?!\s+\*)((?:[^*]+))\*(?!\s+\*))/g, type),
			utils.markPasteRule(/(?:^|\s)(_(?!\s+_)((?:[^_]+))_(?!\s+_))/g, type)
		];
	}

	get schema() {
		return {
			parseDOM: [{ tag: "i" }, { tag: "em" }, { style: "font-style=italic" }],
			toDOM: () => ["em", 0]
		};
	}
}
