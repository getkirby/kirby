import Extension from "../Extension";

/**
 * Minimal Writer Prosemirror extension to show/hide
 * the toolbar when the selection changes
 *
 * All the major logic is handled by <k-writer-toolbar> directly
 */
export default class Toolbar extends Extension {
	constructor(writer) {
		super();
		this.writer = writer;
	}

	get component() {
		return this.writer.$refs.toolbar;
	}

	init() {
		this.editor.on("deselect", ({ event }) => this.component?.close(event));
		this.editor.on("select", ({ hasChanged }) => {
			/**
			 * If the selection did not change,
			 * it does not need to be repositioned,
			 * but the marks still need to be updated
			 */
			if (hasChanged === false) {
				return;
			}

			this.component?.open();
		});
	}

	get type() {
		return "toolbar";
	}
}
