import type { ComponentPublicInstance } from "vue";
import Extension from "../Extension";

/**
 * Minimal Writer ProseMirror extension to show/hide
 * the toolbar when the selection changes
 *
 * All the major logic is handled by <k-writer-toolbar> directly
 */
export default class Toolbar extends Extension {
	private readonly writer: ComponentPublicInstance;

	constructor(writer: ComponentPublicInstance) {
		super();
		this.writer = writer;
	}

	// eslint-disable-next-line @typescript-eslint/no-explicit-any
	get component(): any {
		return this.writer.$refs.toolbar;
	}

	override init(): void {
		// @ts-expect-error – event payload typed once Editor is migrated to TS
		this.editor.on("deselect", ({ event }) => this.component?.close(event));
		// @ts-expect-error – event payload typed once Editor is migrated to TS
		this.editor.on("select", ({ hasChanged }) => {
			/**
			 * If the selection did not change,
			 * it does not need to be repositioned,
			 * but the marks still need to be updated
			 */
			if (hasChanged !== true) {
				return;
			}

			this.component?.open();
		});
	}

	get name() {
		return "toolbar";
	}

	override get type() {
		return "toolbar";
	}
}
