import Mark from "../Mark";

export default class Clear extends Mark {
	get button() {
		return {
			icon: "clear",
			label: window.panel.$t("toolbar.button.clear")
		};
	}

	commands() {
		return () => this.clear();
	}

	clear() {
		const { state } = this.editor;
		const { from, to } = state.tr.selection;

		for (const mark of this.editor.activeMarks) {
			const schema = state.schema.marks[mark];
			const tr = this.editor.state.tr.removeMark(from, to, schema);
			this.editor.view.dispatch(tr);
		}
	}

	get name() {
		return "clear";
	}
}
