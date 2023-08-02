<template>
	<!-- Layout: table -->
	<k-table
		v-if="layout === 'table'"
		v-bind="table"
		@change="$emit('change', $event)"
		@sort="$emit('sort', $event)"
		@option="onOption"
	/>

	<!-- Layout: cards, cardlets, list -->
	<k-draggable
		v-else
		class="k-items"
		:class="'k-' + layout + '-items'"
		:data-layout="layout"
		:data-size="size"
		:handle="true"
		:list="items"
		:options="dragOptions"
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
			default: () => []
		},
		items: {
			type: Array,
			default: () => []
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
			default: () => ({})
		},
		sortable: Boolean,
		/**
		 * Placeholder text and icon for empty state
		 */
		empty: {
			type: [String, Object]
		},
		/**
		 * Card sizes
		 * @values tiny, small, medium, large, huge
		 */
		size: {
			type: String,
			default: "medium"
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
			this.$panel.drag.start("text", dragText);
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
	container-type: inline-size;
}

/** List */
.k-items[data-layout="list"] {
	gap: 2px;
}

/** Cardlets */
.k-items[data-layout="cardlets"] {
	--items-size: 1fr;
	display: grid;
	gap: 0.75rem;
	grid-template-columns: repeat(auto-fill, minmax(var(--items-size), 1fr));
}

@container (min-width: 15rem) {
	.k-items[data-layout="cardlets"] {
		--items-size: 15rem;
	}
}

/** Cards */
.k-items[data-layout="cards"] {
	display: grid;
	gap: 1.5rem;
	grid-template-columns: repeat(
		auto-fill,
		minmax(var(--items-size, 12rem), 1fr)
	);
}

.k-items[data-size="tiny"] {
	--items-size: 6rem;
}
.k-items[data-size="small"] {
	--items-size: 10rem;
}
.k-items[data-size="medium"] {
	--items-size: 12rem;
}
.k-items[data-size="large"] {
	--items-size: 15rem;
}
.k-items[data-size="huge"] {
	--items-size: 1fr;
}

@container (max-width: 6rem) {
	.k-items[data-layout="cards"][data-size="tiny"] {
		grid-template-columns: 1fr;
	}
}
@container (max-width: 9rem) {
	.k-items[data-layout="cards"][data-size="small"] {
		grid-template-columns: 1fr;
	}
}
@container (max-width: 12rem) {
	.k-items[data-layout="cards"][data-size="medium"] {
		grid-template-columns: 1fr;
	}
}
@container (max-width: 15rem) {
	.k-items[data-layout="cards"][data-size="large"] {
		grid-template-columns: 1fr;
	}
}
@container (max-width: 18rem) {
	.k-items[data-layout="cards"][data-size="huge"] {
		grid-template-columns: 1fr;
	}
}
</style>
