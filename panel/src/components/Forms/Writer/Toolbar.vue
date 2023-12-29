<template>
	<k-toolbar
		v-if="isOpen || !inline"
		ref="toolbar"
		:buttons="buttons"
		:data-inline="inline"
		:theme="inline ? 'dark' : 'light'"
		:style="positions"
		class="k-writer-toolbar"
	/>
</template>

<script>
/**
 * Toolbar for `k-writer-input`
 * @displayName WriterToolbar
 * @internal
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
	data() {
		return {
			isOpen: false,
			position: { x: 0, y: 0 }
		};
	},
	computed: {
		/**
		 * The currently active node, if any
		 */
		activeNode() {
			const nodes = Object.values(this.nodeButtons);
			return nodes.find((button) => this.isNodeActive(button)) ?? false;
		},
		/**
		 * Button objects for k-toolbar
		 */
		buttons() {
			const buttons = [];

			// Nodes
			if (this.hasNodes) {
				const nodes = [];

				let nodeIndex = 0;

				for (const nodeType in this.nodeButtons) {
					const node = this.nodeButtons[nodeType];

					nodes.push({
						current: this.activeNode?.id === node.id,
						disabled: this.activeNode?.when?.includes(node.name) === false,
						icon: node.icon,
						label: node.label,
						click: () => this.command(node.command ?? nodeType)
					});

					if (
						node.separator === true &&
						nodeIndex !== Object.keys(this.nodeButtons).length - 1
					) {
						nodes.push("-");
					}

					nodeIndex++;
				}

				buttons.push({
					current: Boolean(this.activeNode),
					icon: this.activeNode.icon ?? "title",
					dropdown: nodes
				});
			}

			// Divider between nodes and marks
			if (this.hasNodes && this.hasMarks) {
				buttons.push("|");
			}

			// Marks
			if (this.hasMarks) {
				for (const markType in this.markButtons) {
					const mark = this.markButtons[markType];

					if (mark === "|") {
						buttons.push("|");
						continue;
					}

					buttons.push({
						current: this.editor.activeMarks.includes(markType),
						icon: mark.icon,
						label: mark.label,
						click: (e) => this.command(mark.command ?? markType, e)
					});
				}
			}

			return buttons;
		},
		/**
		 * Whether there are any marks to show in the toolbar
		 */
		hasMarks() {
			return this.$helper.object.length(this.markButtons) > 0;
		},
		/**
		 * Whether there are any nodes to show in the toolbar
		 */
		hasNodes() {
			return this.$helper.object.length(this.nodeButtons) > 1;
		},
		/**
		 * All marks that are available and requested based on the `marks` prop
		 */
		markButtons() {
			const available = this.editor.buttons("mark");

			if (this.marks === false || this.$helper.object.length(available) === 0) {
				return {};
			}

			if (this.marks === true) {
				return available;
			}

			const buttons = {};

			for (const [index, mark] of this.marks.entries()) {
				if (mark === "|") {
					buttons["divider" + index] = "|";
				} else if (available[mark]) {
					buttons[mark] = available[mark];
				}
			}

			return buttons;
		},
		/**
		 * All nodes that are available and requested based on the `nodes` prop
		 */
		nodeButtons() {
			const available = this.editor.buttons("node");

			if (this.nodes === false || this.$helper.object.length(available) === 0) {
				return {};
			}

			// remove the paragraph when certain nodes are requested to be loaded
			if (this.editor.nodes.doc.content !== "block+" && available.paragraph) {
				delete available.paragraph;
			}

			if (this.nodes === true) {
				return available;
			}

			const buttons = {};

			for (const node of this.nodes) {
				if (available[node]) {
					buttons[node] = available[node];
				}
			}

			return buttons;
		},
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
		 * Checks if the given node is active
		 * @param {Object} node
		 * @returns {Boolean}
		 */
		isNodeActive(node) {
			if (this.editor.activeNodes.includes(node.name) === false) {
				return false;
			}

			// Since the list element also contains a paragraph,
			// don't consider paragraph as an active node when
			// the list item is active
			if (node.name === "paragraph") {
				return (
					this.editor.activeNodes.includes("listItem") === false &&
					this.editor.activeNodes.includes("quote") === false
				);
			}

			// Te might have multiple node buttons for the same node
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
			const menu = document
				.querySelector(".k-panel-menu")
				.getBoundingClientRect();

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
				const left = editor.x + x;
				const right = left + toolbar.width;
				const safeSpaceLeft = menu.width + 20;
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
/** TODO: .k-writer:has(.k-toolbar:not([data-inline="true"])) */
.k-writer-input:not([data-toolbar-inline="true"]):not([data-disabled="true"]) {
	grid-template-areas: "topbar" "content";
	grid-template-rows: var(--toolbar-size) 1fr;
	gap: 0;
}

/** TODO: .k-writer-toolbar:not(:has(~ :focus-within)) */
.k-writer-input:not(:focus-within) {
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
