<template>
	<nav
		v-if="isOpen || !inline"
		class="k-toolbar k-writer-toolbar"
		:data-disabled="disabled"
		:data-inline="inline"
		:style="{
			bottom: position.y + 'px',
			left: position.x + 'px'
		}"
	>
		<!-- Nodes -->
		<k-dropdown v-if="hasNodes" @mousedown.native.prevent>
			<k-button
				:current="Boolean(activeNode)"
				:icon="activeNode.icon ?? 'title'"
				class="k-toolbar-button k-writer-toolbar-nodes"
				@click="$refs.nodes.toggle()"
			/>
			<k-dropdown-content ref="nodes" :theme="inline ? 'light' : 'dark'">
				<template v-for="(node, nodeType, nodeIndex) in nodeButtons">
					<k-dropdown-item
						:key="nodeType"
						:current="activeNode?.id === node.id"
						:disabled="activeNode?.when?.includes(node.name) === false"
						:icon="node.icon"
						@click="command(node.command ?? nodeType)"
					>
						{{ node.label }}
					</k-dropdown-item>
					<hr
						v-if="
							node.separator === true &&
							nodeIndex !== Object.keys(nodeButtons).length - 1
						"
						:key="nodeType + '-divider'"
					/>
				</template>
			</k-dropdown-content>
		</k-dropdown>

		<!-- Divider -->
		<div v-if="hasNodes && hasMarks" class="k-toolbar-divider" />

		<!-- Marks -->
		<template v-for="(mark, markType) in markButtons">
			<div v-if="mark === '|'" :key="markType" class="k-toolbar-divider" />
			<k-button
				v-else
				:key="markType"
				:current="activeMarks.includes(markType)"
				:icon="mark.icon"
				:title="mark.label"
				class="k-toolbar-button"
				@mousedown.native.prevent="command(mark.command ?? markType, $event)"
			/>
		</template>
	</nav>
</template>

<script>
export default {
	props: {
		disabled: {
			type: Boolean
		},
		editor: {
			required: true,
			type: Object
		},
		inline: {
			default: true,
			type: Boolean
		},
		marks: {
			default: true,
			type: [Array, Boolean]
		},
		nodes: {
			default: true,
			type: [Array, Boolean]
		}
	},
	data() {
		return {
			isOpen: false,
			position: {
				x: 0,
				y: 0
			}
		};
	},
	computed: {
		activeMarks() {
			return this.editor.activeMarks;
		},
		activeNodes() {
			return this.editor.activeNodes;
		},
		activeNode() {
			const nodes = Object.values(this.nodeButtons);
			const active = nodes.find((button) => this.isNodeActive(button));
			return active ?? false;
		},
		hasMarks() {
			return this.$helper.object.length(this.markButtons) > 0;
		},
		hasNodes() {
			return this.$helper.object.length(this.nodeButtons) > 1;
		},
		markButtons() {
			if (this.marks === false) {
				return {};
			}

			const available = this.editor.buttons("mark");

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
		nodeButtons() {
			if (this.nodes === false) {
				return {};
			}

			const available = this.editor.buttons("node");

			// remove the paragraph when certain nodes are requested to be loaded
			if (
				Array.isArray(this.nodes) === true &&
				this.nodes.length !== 3 &&
				this.nodes.includes("paragraph") === false &&
				available.paragraph
			) {
				delete available.paragraph;
			}

			if (this.nodes === true) {
				return available;
			}

			const buttons = {};

			for (const node of this.nodes.entries()) {
				if (available[node]) {
					buttons[node] = available[node];
				}
			}

			return buttons;
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
			if (this.activeNodes.includes(node.name) === false) {
				return false;
			}

			// since the list element also contains a paragraph,
			// it is confused whether the list element is an active node
			// this solves the issue
			if (node.name === "paragraph") {
				return this.activeNodes.length === 1;
			}

			if (node.attrs) {
				const attrs = Object.values(this.editor.activeNodeAttrs);
				const active = attrs.find(
					(node) => JSON.stringify(node) === JSON.stringify(node.attrs)
				);

				if (active) {
					return true;
				}
			}

			return false;
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
			const { from, to } = this.editor.selection;

			const start = this.editor.view.coordsAtPos(from);
			const end = this.editor.view.coordsAtPos(to, true);

			// The box in which the tooltip is positioned, to use as base
			const editor = this.editor.element.getBoundingClientRect();

			// Find a center-ish x position from the selection endpoints (when
			// crossing lines, end may be more to the left)
			let left = (start.left + end.left) / 2 - editor.left;
			let bottom = Math.round(editor.bottom - start.top) - 10;

			// Align to writer editor
			const toolbar = this.$el.clientWidth;

			// adjust left overflow
			if (left - toolbar / 2 < 0) {
				left = left + (toolbar / 2 - left) - 10;
			}

			// adjust right overflow
			if (left + toolbar / 2 > editor.width) {
				left = left - (left + toolbar / 2 - editor.width) + 10;
			}

			this.position = {
				y: bottom,
				x: left
			};
		}
	}
};
</script>

<style>
/** TODO: .k-writer:has(.k-writer-toolbar:not([data-inline="true"])) */
.k-writer:not([data-toolbar-inline="true"]):not([data-disabled="true"]) {
	grid-template-areas: "topbar" "content";
	grid-template-rows: var(--toolbar-size) 1fr;
	gap: 0;
}

/** TODO: .k-writer-toolbar:has(~ :focus-within) .k-button[aria-current]  */
.k-writer:focus-within .k-writer-toolbar .k-button[aria-current] {
	color: var(--color-focus);
}

.k-writer-toolbar[data-inline="true"] {
	--toolbar-text: var(--color-white);
	--toolbar-back: var(--color-black);
	--toolbar-hover: rgba(255, 255, 255, 0.2);
	--toolbar-border: var(--color-gray-800);

	position: absolute;
	transform: translateX(-50%) translateY(-0.75rem);
	z-index: calc(var(--z-dropdown) + 1);
	box-shadow: var(--shadow-toolbar);
	border-radius: var(--rounded);
}
</style>
