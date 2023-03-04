<template>
	<div class="k-collection">
		<k-items
			v-if="items.length"
			:columns="columns"
			:items="items"
			:layout="layout"
			:link="link"
			:size="size"
			:sortable="sortable"
			@change="$emit('change', $event)"
			@item="$emit('item', $event)"
			@option="onOption"
			@sort="$emit('sort', $event)"
		>
			<template #options="{ item, itemIndex: index }">
				<slot name="options" v-bind="{ item, index }" />
			</template>
		</k-items>

		<k-empty
			v-else
			v-bind="empty"
			:layout="layout"
			v-on="$listeners['empty'] ? { click: onEmpty } : {}"
		/>

		<footer v-if="hasFooter" class="k-collection-footer">
			<k-text v-if="help" class="k-help k-collection-help" :html="help" />
			<div class="k-collection-pagination">
				<!--
          Emitted when the pagination changes
          @event paginate
          @property {object} pagination
        -->
				<k-pagination
					v-if="hasPagination"
					v-bind="paginationOptions"
					@paginate="$emit('paginate', $event)"
				/>
			</div>
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
		columns: {
			type: [Object, Array],
			default: () => ({})
		},
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
		 * @values list, cards
		 */
		layout: {
			type: String,
			default: "list"
		},
		/**
		 * Enable/disable item links
		 */
		link: {
			type: Boolean,
			default: true
		},
		/**
		 * Size for items in cards layout
		 * @values tiny, small, medium, large, huge
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
		hasFooter() {
			if (this.hasPagination || this.help) {
				return true;
			}

			return false;
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
		onEmpty(e) {
			e.stopPropagation();
			this.$emit("empty");
		},
		onOption(...args) {
			this.$emit("action", ...args);
			this.$emit("option", ...args);
		}
	}
};
</script>

<style>
.k-collection-footer {
	display: flex;
	justify-content: space-between;
	margin-top: 0.5rem;
	margin-inline: -0.75rem;
}
.k-collection-pagination {
	line-height: 1.25rem;
	flex-shrink: 0;
	min-height: 2.75rem;
}
.k-collection-pagination .k-pagination .k-button {
	padding: 0.5rem 0.75rem;
	line-height: 1.125rem;
}
</style>
