import Extension from "./Extension.js";

export default class Mark extends Extension {
	remove() {
		this.editor.removeMark(this.name);
	}

	get schema() {
		return {};
	}

	toggle() {
		return this.editor.toggleMark(this.name);
	}

	get type() {
		return "mark";
	}

	update(attrs) {
		this.editor.updateMark(this.name, attrs);
	}

	get view() {
		return undefined;
	}
}
