<template>
	<k-toolbar
		v-if="isOpen || !inline"
		ref="toolbar"
		:buttons="buttons"
		:data-inline="inline"
		:theme="theme"
		:style="positions"
		class="k-writer-toolbar"
	/>
</template>

<script>
/**
 * Toolbar for `k-writer`
 * @displayName WriterToolbar
 * @unstable
 */
export default {
	props: {
		/**
		 * ProseMirror editor instance
		 */
		editor: {
			required: true,
			type: Object
		},
		/**
		 * Whether the toolbar is displayed inline or as
		 * a floating toolbar near the selection
		 */
		inline: {
			default: true,
			type: Boolean
		},
		/**
		 * Which marks to show in the toolbar
		 */
		marks: {
			default: () => [
				"bold",
				"italic",
				"underline",
				"strike",
				"code",
				"|",
				"link",
				"email",
				"|",
				"clear"
			],
			type: [Array, Boolean]
		},
		/**
		 * Which nodes to show in the toolbar
		 */
		nodes: {
			default: true,
			type: [Array, Boolean]
		}
	},
	emits: ["command"],
	data() {
		return {
			isOpen: false,
			position: { x: 0, y: 0 }
		};
	},
	computed: {
		/**
		 * Currently active dropdown entry, if any
		 * @returns {Object|undefined}
		 */
		activeDropdownEntry() {
			return Object.values(this.dropdownEntries).findLast(this.isNodeActive);
		},
		/**
		 * Button objects for k-toolbar
		 * @returns {Array}
		 */
		buttons() {
			const buttons = [];

			// button for nodes dropdown
			if (this.hasDropdownEntries) {
				buttons.push(this.dropdownInlineButton);
			}

			// divider between dropdown and inline buttons
			if (this.hasDropdownEntries && this.hasInlineEntries) {
				buttons.push("|");
			}

			// inline buttons (all marks and inline nodes)
			for (const [type, entry] of Object.entries(this.inlineEntries)) {
				buttons.push(this.inlineButton(entry, type));
			}

			return buttons;
		},
		/**
		 * All dropdown buttons
		 * @returns {Array}
		 */
		dropdown() {
			const buttons = [];
			const entries = Object.entries(this.dropdownEntries);
			let index = 0;

			for (const [type, entry] of entries) {
				// add dropdown button for each entry
				buttons.push(this.dropdownButton(entry, type));

				// add separator between dropdown entries
				// unless it's the last entry
				if (entry.separator === true && index !== entries.length - 1) {
					buttons.push("-");
				}

				index++;
			}

			return buttons;
		},
		/**
		 * Dropdown inline button for the toolbar
		 * @returns {Object}
		 */
		dropdownInlineButton() {
			return {
				current: Boolean(this.activeDropdownEntry),
				dropdown: this.dropdown,
				icon: this.activeDropdownEntry?.icon ?? "title"
			};
		},
		/**
		 * All dropdown entries that are available and requested
		 * based on the `nodes` prop
		 * @returns {Object}
		 */
		dropdownEntries() {
			if (this.nodes === false) {
				return {};
			}

			// get all non-inline nodes
			const available = this.nodesForBlock;

			// remove the paragraph when certain nodes are requested to be loaded
			if (this.editor.nodes.doc.content !== "block+" && available.paragraph) {
				delete available.paragraph;
			}

			if (this.nodes === true) {
				return available;
			}

			// get requested nodes from available entries
			// if they are available
			return Object.fromEntries(
				this.nodes
					.filter((node) => available[node])
					.map((node) => [node, available[node]])
			);
		},
		/**
		 * Whether there are any block buttons to show in the toolbar dropdown
		 * @returns {Boolean}
		 */
		hasDropdownEntries() {
			return this.$helper.object.length(this.dropdownEntries) > 1;
		},
		/**
		 * Whether there are any inline buttons to show in the toolbar
		 * @returns {Boolean}
		 */
		hasInlineEntries() {
			return this.$helper.object.length(this.inlineEntries) > 0;
		},
		/**
		 * All inline entries that are available and requested
		 * as based on the `marks` and `nodes` props
		 * @returns {Object}
		 */
		inlineEntries() {
			let entries = {};

			// inline nodes
			if (this.nodes === true) {
				// add all inline nodes
				entries = this.nodesForInline;
			} else if (this.nodes !== false) {
				// add requested inline nodes
				for (const node of this.nodes) {
					if (this.nodesForInline[node]) {
						entries[node] = this.nodesForInline[node];
					}
				}
			}

			// add divider between inline nodes and marks
			if (this.$helper.object.length(entries) > 0) {
				entries["divider-inline-nodes"] = "|";
			}

			// marks
			const marks = this.editor.buttons("mark");

			if (this.marks === true) {
				// add all marks to existing entries
				return { ...entries, ...marks };
			}

			if (this.marks !== false) {
				// add only requested marks to existing entries
				for (const [index, mark] of this.marks.entries()) {
					if (mark === "|") {
						entries["divider" + index] = "|";
					} else if (marks[mark]) {
						entries[mark] = marks[mark];
					}
				}
			}

			return entries;
		},
		/**
		 * All block nodes
		 * @returns {Object}
		 */
		nodesForBlock() {
			return this.$helper.object.filter(
				this.editor.buttons("node"),
				(button) => button.inline !== true
			);
		},
		/**
		 * All inline nodes
		 * @returns {Object}
		 */
		nodesForInline() {
			return this.$helper.object.filter(
				this.editor.buttons("node"),
				(button) => button.inline === true
			);
		},
		/**
		 * @returns {Object|null}
		 */
		positions() {
			// only set position when toolbar is inline,
			// otherwise the top value is overwriting the top offset
			// for the sticky non-inline toolbar
			if (this.inline === false) {
				return null;
			}

			return {
				top: this.position.y + "px",
				left: this.position.x + "px"
			};
		},
		/**
		 * @returns {String}
		 */
		theme() {
			return this.inline ? "dark" : "light";
		}
	},
	methods: {
		/**
		 * Closes the inline toolbar
		 * @public
		 * @param {FocusEvent} event
		 */
		close(event) {
			if (!event || this.$el.contains(event.relatedTarget) === false) {
				this.isOpen = false;
			}
		},
		command(command, ...args) {
			this.$emit("command", command, ...args);
		},
		/**
		 * Creates a dropdown button object
		 * @param {Object} entry
		 * @param {String} type
		 * @returns {Object}
		 */
		dropdownButton(entry, type) {
			return {
				current: this.activeDropdownEntry?.id === entry.id,
				disabled:
					this.activeDropdownEntry?.when?.includes(entry.name) === false,
				icon: entry.icon,
				label: entry.label,
				click: () => this.command(entry.command ?? type)
			};
		},
		/**
		 * Creates an inline button object
		 * @param {Object} entry
		 * @param {String} type
		 * @returns {Object}
		 */
		inlineButton(entry, type) {
			if (entry === "|") {
				return "|";
			}

			return {
				current: this.isMarkActive({ ...entry, name: type }),
				icon: entry.icon,
				label: entry.label,
				click: (e) => this.command(entry.command ?? type, e)
			};
		},
		/**
		 * Checks if the given mark is active
		 * @param {Object} mark
		 * @returns {Boolean}
		 */
		isMarkActive(mark) {
			return this.editor.activeMarks.includes(mark.name);
		},
		/**
		 * Checks if the given node is active
		 * @param {Object} node
		 * @returns {Boolean}
		 */
		isNodeActive(node) {
			if (this.editor.activeNodes.includes(node.name) === false) {
				return false;
			}

			// We might have multiple node buttons for the same node
			// (e.g. headings). To know which one is active, we need
			// to compare the active attributes with the
			// attributes of the node button
			if (node.attrs) {
				const activeAttrs = Object.values(this.editor.activeNodeAttrs);
				const activeNode = activeAttrs.find(
					(attrs) => JSON.stringify(attrs) === JSON.stringify(node.attrs)
				);

				if (activeNode === undefined) {
					return false;
				}
			}

			return true;
		},
		/**
		 * Opens the toolbar
		 * @public
		 */
		open() {
			if (this.buttons.length === 0) {
				return;
			}

			this.isOpen = true;

			if (this.inline) {
				this.$nextTick(this.setPosition);
			}
		},
		/**
		 * Calculates the position of the inline toolbar
		 * based on the current selection in the editor
		 */
		setPosition() {
			// Get sizes for the toolbar itself but also the editor box
			const toolbar = this.$el.getBoundingClientRect();
			const editor = this.editor.element.getBoundingClientRect();

			// Create pseudo rectangle for the selection
			const { from, to } = this.editor.selection;
			const start = this.editor.view.coordsAtPos(from);
			const end = this.editor.view.coordsAtPos(to, true);
			const selection = new DOMRect(
				start.left,
				start.top,
				end.right - start.left,
				end.bottom - start.top
			);

			// Calculate the position of the toolbar: centered above the selection
			let x = selection.x - editor.x + selection.width / 2 - toolbar.width / 2;
			let y = selection.y - editor.y - toolbar.height - 5;

			// Contain in editor (if possible)
			if (toolbar.width < editor.width) {
				if (x < 0) {
					x = 0;
				} else if (x + toolbar.width > editor.width) {
					x = editor.width - toolbar.width;
				}
			} else {
				// Contain in viewport
				const menu = document
					.querySelector(".k-panel-menu")
					?.getBoundingClientRect();

				const left = editor.x + x;
				const right = left + toolbar.width;
				const safeSpaceLeft = menu?.width + 20;
				const safeSpaceRight = 20;

				if (left < safeSpaceLeft) {
					x += safeSpaceLeft - left;
				} else if (right > window.innerWidth - safeSpaceRight) {
					x -= right - (window.innerWidth - safeSpaceRight);
				}
			}

			this.position = { x, y };
		}
	}
};
</script>

<style>
.k-writer-input:has(
	.k-toolbar:not([data-inline="true"], [data-disabled="true"])
) {
	grid-template-areas: "topbar" "content";
	grid-template-rows: var(--toolbar-size) 1fr;
	gap: 0;
}

.k-writer-toolbar:not(:has(~ :focus-within)) {
	--toolbar-current: currentColor;
}

.k-writer-toolbar[data-inline="true"] {
	position: absolute;
	z-index: calc(var(--z-dropdown) + 1);
	max-width: none;
	box-shadow: var(--shadow-toolbar);
}
.k-writer-toolbar:not([data-inline="true"]) {
	border-end-start-radius: 0;
	border-end-end-radius: 0;
	border-bottom: 1px solid var(--toolbar-border);
}
.k-writer-toolbar:not([data-inline="true"]) > .k-button:first-child {
	border-end-start-radius: 0;
}
.k-writer-toolbar:not([data-inline="true"]) > .k-button:last-child {
	border-end-end-radius: 0;
}
</style>
