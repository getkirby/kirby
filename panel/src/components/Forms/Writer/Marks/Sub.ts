import type { MarkSpec } from "prosemirror-model";
import Mark from "../Mark";

export default class Sub extends Mark {
	get button() {
		return {
			icon: "subscript",
			label: window.panel.t("toolbar.button.sub")
		};
	}

	commands() {
		return () => this.toggle();
	}

	get name() {
		return "sub";
	}

	get schema(): MarkSpec {
		return {
			parseDOM: [{ tag: "sub" }],
			toDOM: () => ["sub", 0]
		};
	}
}
