import Mark from "../Mark";

export default class Sup extends Mark {
	get button() {
		return {
			icon: "subscript",
			label: window.panel.$t("toolbar.button.sub")
		};
	}

	commands() {
		return () => this.toggle();
	}

	get name() {
		return "sub";
	}

	get schema() {
		return {
			parseDOM: [{ tag: "sub" }],
			toDOM: () => ["sub", 0]
		};
	}
}
