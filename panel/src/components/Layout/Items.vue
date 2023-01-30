<template>
	<k-table
		v-if="layout === 'table'"
		v-bind="table"
		@change="$emit('change', $event)"
		@sort="$emit('sort', $event)"
		@option="onOption"
	/>
	<k-draggable
		v-else
		class="k-items"
		:class="'k-' + layout + '-items'"
		:handle="true"
		:options="dragOptions"
		:data-layout="layout"
		:data-size="size"
		:list="items"
		@change="$emit('change', $event)"
		@end="$emit('sort', items, $event)"
	>
		<template v-for="(item, itemIndex) in items">
			<slot v-bind="{ item, itemIndex }">
				<k-item
					:key="item.id || itemIndex"
					v-bind="item"
					:class="{ 'k-draggable-item': sortable && item.sortable }"
					:image="imageOptions(item)"
					:layout="layout"
					:link="link ? item.link : false"
					:sortable="sortable && item.sortable"
					:width="item.column"
					@click="$emit('item', item, itemIndex)"
					@drag="onDragStart($event, item.dragText)"
					@mouseover.native="$emit('hover', $event, item, itemIndex)"
					@option="onOption($event, item, itemIndex)"
				>
					<template #options>
						<slot name="options" v-bind="{ item, itemIndex }" />
					</template>
				</k-item>
			</slot>
		</template>
	</k-draggable>
</template>

<script>
export default {
	inheritAttrs: false,
	props: {
		columns: {
			type: [Object, Array],
			default() {
				return {};
			}
		},
		items: {
			type: Array,
			default() {
				return [];
			}
		},
		layout: {
			type: String,
			default: "list"
		},
		link: {
			type: Boolean,
			default: true
		},
		image: {
			type: [Object, Boolean],
			default() {
				return {};
			}
		},
		sortable: Boolean,
		/**
		 * Placeholder text and icon for empty state
		 */
		empty: {
			type: [String, Object]
		},
		/**
		 * Card sizes.
		 */
		size: {
			type: String,
			default: "default"
		}
	},
	computed: {
		dragOptions() {
			return {
				sort: this.sortable,
				disabled: this.sortable === false,
				draggable: ".k-draggable-item"
			};
		},
		table() {
			let columns = this.columns;
			let items = this.items;

			return {
				columns: columns,
				rows: items,
				sortable: this.sortable
			};
		}
	},
	methods: {
		onDragStart($event, dragText) {
			this.$store.dispatch("drag", {
				type: "text",
				data: dragText
			});
		},
		onOption(option, item, itemIndex) {
			this.$emit("option", option, item, itemIndex);
		},
		imageOptions(item) {
			let globalOptions = this.image;
			let localOptions = item.image;

			if (globalOptions === false || localOptions === false) {
				return false;
			}

			if (typeof globalOptions !== "object") {
				globalOptions = {};
			}

			if (typeof localOptions !== "object") {
				localOptions = {};
			}

			return {
				...localOptions,
				...globalOptions
			};
		}
	}
};
</script>

<style>
.k-items {
	position: relative;
	display: grid;
}

/**
 * Cards
 */
.k-cards-items {
	--items-layout-mode: auto-fit;
	display: grid;
	gap: 1.5rem;
	grid-template-columns: repeat(
		var(--items-layout-mode),
		minmax(var(--items-size, 12rem), 1fr)
	);
}

.k-cards-items[data-size="tiny"] {
	--items-size: 6rem;
}
.k-cards-items[data-size="small"] {
	--items-size: 9rem;
}
.k-cards-items[data-size="medium"] {
	--items-size: 12rem;
}
.k-cards-items[data-size="large"] {
	--items-size: 15rem;
}
.k-cards-items[data-size="huge"] {
	--items-size: 18rem;
}

@container (max-width: 6rem) {
	.k-cards-items[data-size="tiny"] {
		grid-template-columns: repeat(1, 1fr);
	}
}
@container (max-width: 9rem) {
	.k-cards-items[data-size="small"] {
		grid-template-columns: repeat(1, 1fr);
	}
}
@container (max-width: 12rem) {
	.k-cards-items[data-size="medium"] {
		grid-template-columns: repeat(1, 1fr);
	}
}
@container (max-width: 15rem) {
	.k-cards-items[data-size="large"] {
		grid-template-columns: repeat(1, 1fr);
	}
}
@container (max-width: 18rem) {
	.k-cards-items[data-size="huge"] {
		grid-template-columns: repeat(1, 1fr);
	}
}

/**
 * Cardlets
 */
.k-cardlets-items {
	display: grid;
	gap: 0.75rem;
	grid-template-columns: repeat(auto-fit, minmax(15rem, 1fr));
}

/**
 * List
 */
.k-list-items {
	gap: 2px;
}
</style>
