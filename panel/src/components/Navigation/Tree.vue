<template>
	<ul
		:class="['k-tree', $options.name, $attrs.class]"
		:style="{ '--tree-level': level, ...$attrs.style }"
	>
		<li
			v-for="item in state"
			:key="item.value"
			:aria-expanded="item.open"
			:aria-current="isItem(item, current)"
		>
			<p class="k-tree-branch">
				<button
					:disabled="!item.hasChildren"
					class="k-tree-toggle"
					type="button"
					@click="toggle(item)"
				>
					<k-icon :type="arrow(item)" />
				</button>
				<button
					:disabled="item.disabled"
					class="k-tree-folder"
					type="button"
					@click="select(item)"
					@dblclick="toggle(item)"
				>
					<k-icon-frame :icon="item.icon ?? 'folder'" />
					<span class="k-tree-folder-label">{{ item.label }}</span>
				</button>
			</p>
			<template v-if="item.hasChildren && item.open">
				<component
					:is="$options.name"
					:ref="item.value"
					v-bind="$props"
					:items="item.children"
					:level="level + 1"
					@close="$emit('close', $event)"
					@open="$emit('open', $event)"
					@select="$emit('select', $event)"
					@toggle="$emit('toggle', $event)"
				/>
			</template>
		</li>
	</ul>
</template>

<script>
/**
 * @displayName Tree
 * @since 4.0.0
 */
export default {
	name: "k-tree",
	inheritAttrs: false,
	props: {
		element: {
			type: String,
			default: "k-tree"
		},
		current: {
			type: String
		},
		items: {
			type: [Array, Object]
		},
		level: {
			default: 0,
			type: Number
		}
	},
	emits: ["close", "open", "select", "toggle"],
	data() {
		return {
			state: this.items
		};
	},
	methods: {
		arrow(item) {
			if (item.loading === true) {
				return "loader";
			}

			return item.open ? "angle-down" : "angle-right";
		},
		close(item) {
			this.$set(item, "open", false);
			this.$emit("close", item);
		},
		isItem(item, target) {
			return item.value === target;
		},
		open(item) {
			this.$set(item, "open", true);
			this.$emit("open", item);
		},
		select(item) {
			this.$emit("select", item);
		},
		toggle(item) {
			this.$emit("toggle", item);

			if (item.open === true) {
				this.close(item);
			} else {
				this.open(item);
			}
		}
	}
};
</script>

<style>
:root {
	--tree-color-back: var(--panel-color-back);
	--tree-indentation: 0.6rem;
	--tree-level: 0;

	--tree-branch-color-back: var(--tree-color-back);
	--tree-branch-color-text: var(--color-text-dimmed);
	--tree-branch-hover-color-back: var(--browser-item-hover-color-back);
	--tree-branch-hover-color-text: var(--browser-item-hover-color-text);
	--tree-branch-selected-color-back: var(--browser-item-selected-color-back);
	--tree-branch-selected-color-text: var(--browser-item-selected-color-text);
}

.k-tree-branch {
	display: flex;
	align-items: center;
	padding-inline-start: calc(var(--tree-level) * var(--tree-indentation));
	margin-bottom: 1px;
	background: var(--tree-branch-color-back);
}
.k-tree-branch:has(+ .k-tree) {
	inset-block-start: calc(var(--tree-level) * 1.5rem);
	z-index: calc(100 - var(--tree-level));
}
.k-tree-branch:hover,
li[aria-current="true"] > .k-tree-branch {
	color: var(--tree-branch-hover-color-text);
	background: var(--tree-branch-hover-color-back);
	border-radius: var(--rounded);
}
li[aria-current="true"] > .k-tree-branch {
	background: var(--tree-branch-selected-color-back);
}
.k-tree-toggle {
	--icon-size: 12px;
	width: 1rem;
	aspect-ratio: 1/1;
	display: grid;
	place-items: center;
	padding: 0;
	border-radius: var(--rounded-sm);
	margin-inline-start: 0.25rem;
	flex-shrink: 0;
}
.k-tree-toggle:hover {
	background: rgba(0, 0, 0, 0.075);
}
.k-tree-toggle[disabled] {
	visibility: hidden;
}
.k-tree-folder {
	display: flex;
	height: var(--height-sm);
	border-radius: var(--rounded-sm);
	padding-inline: 0.25rem;
	width: 100%;
	align-items: center;
	gap: 0.325rem;
	min-width: 3rem;
	line-height: 1.25;
	font-size: var(--text-sm);
}

@container (max-width: 15rem) {
	.k-tree {
		--tree-indentation: 0.375rem;
	}
	.k-tree-folder {
		padding-inline: 0.125rem;
	}
	.k-tree-folder .k-icon {
		display: none;
	}
}
.k-tree-folder > .k-frame {
	flex-shrink: 0;
}
.k-tree-folder-label {
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
	color: currentColor;
}

.k-tree-folder[disabled] {
	opacity: var(--opacity-disabled);
}
</style>
