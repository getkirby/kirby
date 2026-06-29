import type { MarkSpec } from "prosemirror-model";
import Mark from "../Mark";

/**
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
export default class Sup extends Mark {
	get button() {
		return {
			icon: "superscript",
			label: window.panel.t("toolbar.button.sup")
		};
	}

	commands() {
		return () => this.toggle();
	}

	get name() {
		return "sup";
	}

	get schema(): MarkSpec {
		return {
			parseDOM: [{ tag: "sup" }],
			toDOM: () => ["sup", 0]
		};
	}
}
