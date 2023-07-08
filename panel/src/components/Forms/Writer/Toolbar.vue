<template>
	<nav
		class="k-toolbar k-writer-toolbar"
		:data-inline="inline"
		:style="inline ? 'display: none' : null"
	>
		<k-dropdown v-if="hasVisibleNodeButtons" @mousedown.native.prevent>
			<k-button
				:current="!!activeNodeButton"
				:icon="activeNodeButton.icon || 'title'"
				class="k-toolbar-button k-writer-toolbar-nodes"
				@click="$refs.nodes.toggle()"
			/>
			<k-dropdown-content ref="nodes" theme="light">
				<template v-for="(node, nodeType, nodeIndex) in nodeButtons">
					<k-dropdown-item
						:key="nodeType"
						:current="activeNodeButton?.id === node.id"
						:disabled="activeNodeButton?.when?.includes(node.name) === false"
						:icon="node.icon"
						@click="command(node.command || nodeType)"
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

		<div
			v-if="hasVisibleNodeButtons && hasVisibleMarkButtons"
			class="k-toolbar-divider"
		/>

		<template v-for="(mark, markType) in markButtons">
			<div v-if="mark === '|'" :key="markType" class="k-toolbar-divider" />
			<k-button
				v-else
				:key="markType"
				:current="activeMarks.includes(markType)"
				:icon="mark.icon"
				:title="mark.label"
				class="k-toolbar-button"
				@mousedown.native.prevent="command(mark.command || markType, $event)"
			/>
		</template>
	</nav>
</template>

<script>
export default {
	props: {
		activeMarks: Array,
		activeNodes: Array,
		activeNodeAttrs: {
			type: [Array, Object],
			default: () => []
		},
		editor: {
			type: Object,
			required: true
		},
		inline: {
			type: Boolean,
			default: true
		},
		isParagraphNodeHidden: Boolean,
		marks: {
			type: [Array, Boolean],
			default: true
		},
		nodes: {
			type: [Array, Boolean],
			default: true
		}
	},
	computed: {
		activeNodeButton() {
			return (
				Object.values(this.nodeButtons).find((button) =>
					this.isButtonActive(button)
				) || false
			);
		},
		hasVisibleMarkButtons() {
			return this.$helper.object.length(this.markButtons) > 0;
		},
		hasVisibleNodeButtons() {
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
					continue;
				}
				if (available[mark]) {
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
			if (this.isParagraphNodeHidden === true && available.paragraph) {
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
		command(command, ...args) {
			this.$emit("command", command, ...args);
		},
		isButtonActive(button) {
			// since the list element also contains a paragraph,
			// it is confused whether the list element is an active node
			// this solves the issue
			if (button.name === "paragraph") {
				return (
					this.activeNodes.length === 1 &&
					this.activeNodes.includes(button.name)
				);
			}

			let isActiveNodeAttr = true;

			if (button.attrs) {
				const activeNodeAttrs = Object.values(this.activeNodeAttrs).find(
					(node) => JSON.stringify(node) === JSON.stringify(button.attrs)
				);

				isActiveNodeAttr = Boolean(activeNodeAttrs || false);
			}

			return (
				isActiveNodeAttr === true && this.activeNodes.includes(button.name)
			);
		}
	}
};
</script>

<style>
.k-writer:has(.k-writer-toolbar:not([data-inline="true"])) {
	grid-template-areas: "topbar" "content";
	grid-template-rows: var(--toolbar-size) 1fr;
	gap: 0;
}

.k-writer-toolbar:has(~ :focus-within) .k-button[aria-current] {
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
