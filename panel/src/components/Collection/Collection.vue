<template>
	<div class="k-collection">
		<k-empty
			v-if="items.length === 0"
			v-bind="empty"
			:layout="layout"
			v-on="listeners"
		/>

		<component
			:is="component"
			v-else
			v-bind="{
				items,
				selecting,
				selectmode,
				selected,
				sortable
			}"
			:options="{
				columns,
				fields,
				link,
				image,
				layout,
				size,
				theme,
				...options
			}"
			@change="$emit('change', $event)"
			@item="$emit('item', $event)"
			@option="onOption"
			@select="onSelect"
			@sort="$emit('sort', $event)"
		>
			<template v-if="$slots.options" #options="{ item, index }">
				<slot name="options" v-bind="{ item, index }" />
			</template>
		</component>

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
import { getCurrentInstance } from "vue";
import { props as CollectionLayoutProps } from "./CollectionLayout.vue";
import { layout } from "@/mixins/props.js";

/**
 * The `k-collection` component is a wrapper around `k-items`
 * that adds sortabilty and pagination to the items.
 */
export default {
	mixins: [CollectionLayoutProps, layout],
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
		},

		/**
		 * @deprecated Use `options` for layout-specific options
		 */
		columns: {
			type: [Object, Array],
			default: () => ({})
		},
		fields: {
			type: Object,
			default: () => ({})
		},
		image: {
			type: [Object, Boolean],
			default: () => ({})
		},
		link: {
			type: Boolean,
			default: true
		},
		size: {
			type: String,
			default: "medium"
		},
		theme: String
	},
	emits: [
		"action",
		"change",
		"empty",
		"item",
		"option",
		"paginate",
		"select",
		"sort"
	],
	computed: {
		component() {
			if (
				this.layout === "cards" ||
				this.layout === "cardlets" ||
				this.layout === "list"
			) {
				return "k-items-collection-layout";
			}

			return `k-${this.layout}-collection-layout`;
		},
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
			const instance = getCurrentInstance();

			if (instance?.vnode?.props?.onEmpty !== undefined) {
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
