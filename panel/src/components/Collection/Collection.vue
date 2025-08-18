<template>
	<div class="k-collection">
		<k-empty
			v-if="items.length === 0"
			v-bind="empty"
			:layout="layout"
			v-on="listeners"
		/>

		<k-items
			v-else
			v-bind="{
				columns,
				fields,
				items,
				layout,
				link,
				selecting,
				size,
				sortable,
				theme
			}"
			@change="$emit('change', $event)"
			@input="(...args) => $emit('input', ...args)"
			@item="$emit('item', $event)"
			@option="onOption"
			@select="onSelect"
			@sort="$emit('sort', $event)"
		>
			<template #options="{ item, index }">
				<slot name="options" v-bind="{ item, index }" />
			</template>
		</k-items>

		<footer v-if="help || hasPagination" class="k-collection-footer">
			<k-text class="k-help k-collection-help" :html="help" />
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
		</footer>
	</div>
</template>

<script>
import { props as ItemsProps } from "./Items.vue";

/**
 * The `k-collection` component is a wrapper around `k-items`
 * that adds sortabilty and pagination to the items.
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
	emits: ["action", "change", "empty", "item", "option", "paginate", "sort"],
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
		listeners() {
			if (this.$listeners["empty"]) {
				return {
					click: this.onEmpty
				};
			}
			return {};
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
		},
		onSelect(...args) {
			this.$emit("select", ...args);
		}
	}
};
</script>

<style>
.k-collection-footer {
	display: flex;
	justify-content: space-between;
	align-items: flex-start;
	flex-wrap: nowrap;
	gap: var(--spacing-12);
	margin-top: var(--spacing-2);
}
</style>
