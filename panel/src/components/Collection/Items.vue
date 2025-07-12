<template>
	<!-- Layout: table -->
	<k-table
		v-if="layout === 'table'"
		v-bind="table"
		:class="$attrs.class"
		:style="$attrs.style"
		@change="$emit('change', $event)"
		@select="onSelect"
		@sort="$emit('sort', $event)"
		@option="onOption"
	>
		<template
			v-if="$scopedSlots.options"
			#options="{ row: item, rowIndex: index }"
		>
			<slot name="options" v-bind="{ item, index }" />
		</template>
	</k-table>

	<!-- Layout: cards, cardlets, list -->
	<k-draggable
		v-else
		:class="['k-items', 'k-' + layout + '-items', $attrs.class]"
		:data-layout="layout"
		:data-size="size"
		:handle="true"
		:list="items"
		:options="dragOptions"
		:style="$attrs.style"
		@change="$emit('change', $event)"
		@end="$emit('sort', items, $event)"
	>
		<template v-for="(item, itemIndex) in items">
			<slot v-bind="{ item, itemIndex }">
				<k-item
					:key="item.id ?? itemIndex"
					v-bind="item"
					:class="{ 'k-draggable-item': sortable && item.sortable }"
					:image="imageOptions(item)"
					:layout="layout"
					:link="link ? item.link : false"
					:selecting="selecting"
					:selectable="item.selectable"
					:sortable="sortable && item.sortable"
					:theme="item.theme ?? theme"
					:width="item.column"
					@click="$emit('item', item, itemIndex)"
					@drag="onDragStart($event, item.dragText)"
					@mouseover.native="$emit('hover', $event, item, itemIndex)"
					@option="onOption($event, item, itemIndex)"
					@select="onSelect(item, itemIndex)"
				>
					<template #options>
						<slot name="options" v-bind="{ item, index: itemIndex }" />
					</template>
				</k-item>
			</slot>
		</template>
	</k-draggable>
</template>

<script>
import { layout } from "@/mixins/props.js";
/**
 * Collection items that can be displayed in various layouts
 */
export const props = {
	mixins: [layout],
	inheritAttrs: false,
	props: {
		/**
		 * Optional column settings for the table layout
		 */
		columns: {
			type: [Object, Array],
			default: () => ({})
		},
		/**
		 * Optional fields configuration that is used for table layout
		 * @unstable
		 */
		fields: {
			type: Object,
			default: () => ({})
		},
		/**
		 * Array of item definitions. See `k-item` for available options.
		 */
		items: {
			type: Array,
			default: () => []
		},
		/**
		 * Enable/disable that each item is a clickable link
		 */
		link: {
			type: Boolean,
			default: true
		},
		/**
		 * Whether items are in selecting mode
		 */
		selecting: Boolean,
		/**
		 * Whether items are generally sortable.
		 * Each item can disable this individually.
		 */
		sortable: Boolean,
		/**
		 * Card sizes
		 * @values "tiny", "small", "medium", "large", "huge", "full"
		 */
		size: {
			type: String,
			default: "medium"
		},
		/**
		 * Visual theme for items
		 * @values "disabled"
		 */
		theme: String
	}
};

export default {
	mixins: [props],
	props: {
		/**
		 * Globale image/icon settings. Will be merged with the image settings of each item. See `k-item-image` for available options.
		 */
		image: {
			type: [Object, Boolean],
			default: () => ({})
		}
	},
	emits: ["change", "hover", "item", "option", "select", "sort"],
	computed: {
		dragOptions() {
			return {
				sort: this.sortable,
				disabled: this.sortable === false,
				draggable: ".k-draggable-item"
			};
		},
		table() {
			return {
				columns: this.columns,
				fields: this.fields,
				rows: this.items,
				selecting: this.selecting,
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
		onSelect(event, item, itemIndex) {
			this.$emit("select", event, item, itemIndex);
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
	grid-template-columns: 1fr;
}

@container (min-width: 6rem) {
	.k-items[data-layout="cards"][data-size="tiny"] {
		grid-template-columns: repeat(auto-fill, minmax(6rem, 1fr));
	}
}
@container (min-width: 9rem) {
	.k-items[data-layout="cards"][data-size="small"] {
		grid-template-columns: repeat(auto-fill, minmax(9rem, 1fr));
	}
}
@container (min-width: 12rem) {
	.k-items[data-layout="cards"][data-size="auto"],
	.k-items[data-layout="cards"][data-size="medium"] {
		grid-template-columns: repeat(auto-fill, minmax(12rem, 1fr));
	}
}
@container (min-width: 15rem) {
	.k-items[data-layout="cards"][data-size="large"] {
		grid-template-columns: repeat(auto-fill, minmax(15rem, 1fr));
	}
}
@container (min-width: 18rem) {
	.k-items[data-layout="cards"][data-size="huge"] {
		grid-template-columns: repeat(auto-fill, minmax(18rem, 1fr));
	}
}
</style>
