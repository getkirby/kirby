<template>
	<div class="k-collection">
		<k-empty
			v-if="items.length === 0"
			v-bind="empty"
			:layout="layout"
			v-on="$listeners['empty'] ? { click: onEmpty } : {}"
		/>

		<k-items
			v-else
			:columns="columns"
			:items="items"
			:layout="layout"
			:link="link"
			:size="size"
			:sortable="sortable"
			@change="$emit('change', $event)"
			@item="$emit('item', $event)"
			@option="onOption"
			@sort="onSort"
		>
			<template #options="{ item, index }">
				<!--
					@slot Replaces otions for each item from `options` key of item
					@binding {object} item
					@binding {number} index
				-->
				<slot name="options" v-bind="{ item, index }" />
			</template>
		</k-items>

		<footer v-if="help || hasPagination" class="k-bar k-collection-footer">
			<k-text class="k-help k-collection-help" :html="help" />
			<!--
				Pagination has changed
				@event paginate
				@property {object} pagination
			-->
			<k-pagination
				v-if="hasPagination"
				v-bind="paginationOptions"
				@paginate="$emit('paginate', $event)"
			/>
		</footer>
	</div>
</template>

<script>
/**
 * The `k-collection` component is a wrapper around `k-items`
 * that adds sortabilty and pagination to the items.
 */
export default {
	props: {
		/**
		 * Optional column settings when using the table layout
		 */
		columns: {
			type: [Object, Array],
			default: () => ({})
		},
		/**
		 * Empty state, see `k-empty` for all options
		 */
		empty: Object,
		/**
		 * Help text to show below the collection
		 */
		help: String,
		items: {
			type: [Array, Object],
			default: () => []
		},
		/**
		 * Layout of the collection
		 * @values "list", "cardlets", "cards", "table"
		 */
		layout: {
			type: String,
			default: "list"
		},
		/**
		 * Enable/disable that each item is a clickable link
		 */
		link: {
			type: Boolean,
			default: true
		},
		/**
		 * Size for items in cards layout
		 * @values "tiny", "small", "medium", "large", "huge"
		 */
		size: String,
		/**
		 * Whether the collection can be sorted
		 */
		sortable: Boolean,
		pagination: {
			type: [Boolean, Object],
			default: false
		}
	},
	computed: {
		hasPagination() {
			if (this.pagination === false) {
				return false;
			}

			if (this.paginationOptions.hide === true) {
				return false;
			}

			if (this.pagination.total <= this.pagination.limit) {
				return false;
			}

			return true;
		},
		paginationOptions() {
			const options =
				typeof this.pagination !== "object" ? {} : this.pagination;
			return {
				limit: 10,
				details: true,
				keys: false,
				total: 0,
				hide: false,
				...options
			};
		}
	},
	watch: {
		$props() {
			this.$forceUpdate();
		}
	},
	methods: {
		onEmpty(event) {
			event.stopPropagation();

			/**
			 * Empty collection has been clicked
			 * @property {PointerEvent} event
			 */
			this.$emit("empty", event);
		},
		onOption(...args) {
			this.$emit("action", ...args);
			this.$emit("option", ...args);
		},
		onSort(items) {
			/**
			 * Items have been sorted
			 * @property {array} items
			 */
			this.$emit("sort", items);
		}
	}
};
</script>

<style>
.k-collection-footer {
	margin-top: var(--spacing-2);
	flex-wrap: wrap;
}
</style>
