import Mark from "../Mark";

export default class Sup extends Mark {
	get button() {
		return {
			icon: "superscript",
			label: window.panel.$t("toolbar.button.sup")
		};
	}

	commands() {
		return () => this.toggle();
	}

	get name() {
		return "sup";
	}

	get schema() {
		return {
			parseDOM: [{ tag: "sup" }],
			toDOM: () => ["sup", 0]
		};
	}
}
