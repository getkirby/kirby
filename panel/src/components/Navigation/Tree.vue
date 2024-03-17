<template>
	<ul
		:class="['k-tree', $options.name, $attrs.class]"
		:style="{ '--tree-level': level, ...$attrs.style }"
	>
		<li
			v-for="(item, index) in state"
			:key="index"
			:aria-expanded="item.open"
			:aria-current="item.value === current"
		>
			<p
				class="k-tree-branch"
				:data-has-subtree="item.hasChildren && item.open"
			>
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
	--tree-color-back: var(--color-gray-200);
	--tree-color-hover-back: var(--color-gray-300);
	--tree-color-selected-back: var(--color-blue-300);
	--tree-color-selected-text: var(--color-black);
	--tree-color-text: var(--color-gray-dimmed);
	--tree-level: 0;
	--tree-indentation: 0.6rem;
}

.k-tree-branch {
	display: flex;
	align-items: center;
	padding-inline-start: calc(var(--tree-level) * var(--tree-indentation));
	margin-bottom: 1px;
}
/** TODO: .k-tree-branch:has(+ .k-tree)  */
.k-tree-branch[data-has-subtree="true"] {
	inset-block-start: calc(var(--tree-level) * 1.5rem);
	z-index: calc(100 - var(--tree-level));
	background: var(--tree-color-back);
}
.k-tree-branch:hover,
li[aria-current="true"] > .k-tree-branch {
	--tree-color-text: var(--tree-color-selected-text);
	background: var(--tree-color-hover-back);
	border-radius: var(--rounded);
}
li[aria-current="true"] > .k-tree-branch {
	background: var(--tree-color-selected-back);
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
