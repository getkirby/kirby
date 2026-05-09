import type { MarkSpec } from "prosemirror-model";
import Mark from "../Mark";

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

		const { from, to } = state.tr.selection;

		for (const mark of this.editor.activeMarks) {
			const schema = state.schema.marks[mark];
			const tr = state.tr.removeMark(from, to, schema);
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
