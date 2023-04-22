import Extension from "../Extension";

export default class Toolbar extends Extension {
	constructor(options = {}) {
		super(options);
	}

	close() {
		this.visible = false;
	}

	init() {
		this.editor.on("blur", () => this.close());

		this.editor.on("deselect", () => this.close());

		this.editor.on("select", ({ hasChanged }) => {
			/**
			 * If the selection did not change,
			 * it does not need to be repositioned,
			 * but the marks still need to be updated
			 */
			if (hasChanged === false) {
				return;
			}

			this.open();
		});
	}

	open() {
		this.visible = true;

		if (this.options.inline) {
			this.options.writer.$nextTick(() => this.reposition());
		}
	}

	set position(position) {
		if (this.options.inline) {
			this.toolbar.style.bottom = position.bottom + "px";
			this.toolbar.style.left = position.left + "px";
		}
	}

	reposition() {
		const { from, to } = this.editor.selection;

		const start = this.editor.view.coordsAtPos(from);
		const end = this.editor.view.coordsAtPos(to, true);

		// The box in which the tooltip is positioned, to use as base
		const editorRect = this.editor.element.getBoundingClientRect();

		// Find a center-ish x position from the selection endpoints (when
		// crossing lines, end may be more to the left)
		let left = (start.left + end.left) / 2 - editorRect.left;
		let bottom = Math.round(editorRect.bottom - start.top);

		// Align to writer editor
		const editorWidth = editorRect.clientWidth;
		const toolbarWidth = this.toolbar.clientWidth;

		// adjust left overflow
		if (left - toolbarWidth / 2 < 0) {
			left = left + (toolbarWidth / 2 - left) - 20;
		}

		// adjust right overflow
		if (left + toolbarWidth / 2 > editorWidth) {
			left = left - (left + toolbarWidth / 2 - editorWidth) + 20;
		}

		this.position = {
			bottom,
			left
		};
	}

	get toolbar() {
		return this.editor.element.querySelector(".k-writer-toolbar");
	}

	get type() {
		return "toolbar";
	}

	get visible() {
		return this.toolbar?.style.display === "flex";
	}

	set visible(visible) {
		if (this.options.inline && this.toolbar) {
			if (visible) {
				this.toolbar.style.display = "flex";
			} else {
				this.toolbar.style.display = "none";
			}
		}
	}
}
