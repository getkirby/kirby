import type { MarkSpec } from "prosemirror-model";
import Mark from "../Mark";

/**
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
export default class Clear extends Mark {
	get button() {
		return {
			icon: "clear",
			label: window.panel.t("toolbar.button.clear")
		};
	}

	clear() {
		const { state, view } = this.editor;

		if (!state || !view) {
			return;
		}

		// one transaction for all marks: a transaction built from
		// the state before an earlier dispatch can no longer be applied
		const { from, to } = state.selection;
		const tr = state.tr;

		for (const mark of this.editor.activeMarks) {
			tr.removeMark(from, to, state.schema.marks[mark]);
		}

		if (tr.docChanged === true) {
			view.dispatch(tr);
		}
	}

	commands() {
		return () => this.clear();
	}

	get name() {
		return "clear";
	}

	get schema(): MarkSpec {
		return {};
	}
}
