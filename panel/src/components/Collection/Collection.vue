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
import { props as ItemsProps } from "./Items.vue";

/**
 * The `k-collection` component is a wrapper around `k-items`
 * that adds pagination, empty state and help text to the items.
 */
export default {
	mixins: [ItemsProps],
	props: {
		/**
		 * Empty state, see `k-empty` for all options
		 */
		empty: {
			type: Object,
			default: () => ({})
		},
		/**
		 * Help text to show below the collection
		 */
		help: String,
		/**
		 * Whether pagination should be shown, and if,
		 * pagination options (see `k-pagination` for details)
		 */
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
