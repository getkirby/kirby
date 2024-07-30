import Mark from "../Mark";

export default class Code extends Mark {
	get button() {
		return {
			icon: "code",
			label: window.panel.$t("toolbar.button.code")
		};
	}

	commands() {
		return () => this.toggle();
	}

	inputRules({ type, utils }) {
		return [
			utils.markInputRule(/(?:^|\s)(`(?!\s+`)((?:[^`]+))`(?!\s+`))$/, type)
		];
	}

	keys() {
		return {
			"Mod-`": () => this.toggle()
		};
	}

	get name() {
		return "code";
	}

	pasteRules({ type, utils }) {
		return [
			utils.markPasteRule(/(?:^|\s)(`(?!\s+`)((?:[^`]+))`(?!\s+`))/g, type)
		];
	}

	get schema() {
		return {
			excludes: "_",
			parseDOM: [{ tag: "code" }],
			toDOM: () => ["code", 0]
		};
	}
}
