<template>
	<k-dialog
		v-bind="$props"
		class="k-model-picker-dialog"
		@cancel="$emit('cancel')"
		@drop="$emit('drop', $event)"
		@submit="submit"
	>
		<slot name="header" />

		<slot name="search">
			<header class="k-model-picker-dialog-header">
				<k-dialog-search
					v-if="hasSearch"
					:value="query"
					class="k-model-picker-dialog-search"
					@search="query = $event"
				/>
				<slot name="buttons" />
			</header>
		</slot>

		<k-collection v-bind="collection">
			<template #options="{ item }">
				<slot name="options" v-bind="{ item }" />
			</template>
		</k-collection>
	</k-dialog>
</template>

<script>
import Dialog from "@/mixins/dialog.js";
import Search from "@/mixins/search.js";

export const props = {
	mixins: [Dialog, Search],
	props: {
		/**
		 * Empty state for the collection
		 * @value { icon, text}
		 */
		empty: {
			type: Object,
			default: () => ({})
		},
		help: {
			type: String
		},
		/**
		 * Selectable models
		 */
		items: {
			type: Array,
			default: () => []
		},
		/**
		 * Layout of the collection
		 * @values "list"|"cardlets"|"cards"
		 */
		layout: {
			type: String,
			default: "list"
		},
		/**
		 * Maximum number of selectable models
		 */
		max: Number,
		/**
		 * Whether multiple models can be selected
		 */
		multiple: {
			type: Boolean,
			default: true
		},
		/**
		 * Current pagination state
		 */
		pagination: {
			type: Object,
			default: () => ({})
		},
		size: {
			default: "medium"
		},
		/**
		 * Selected models
		 */
		value: {
			type: Array,
			default: () => []
		}
	}
};

export default {
	mixins: [props],
	props: {
		/**
		 * Payload to send along dialog refreshes
		 */
		payload: {
			type: Object,
			default: () => ({})
		}
	},
	emits: ["cancel", "drop", "submit"],
	data() {
		return {
			selected: this.value
		};
	},
	computed: {
		collection() {
			return {
				empty: {
					...this.empty,
					text: this.$panel.dialog.isLoading
						? this.$t("loading")
						: this.empty.text
				},
				help: this.help,
				items: this.items,
				layout: this.layout,
				link: false,
				pagination: {
					details: true,
					dropdown: false,
					align: "center",
					...this.pagination
				},
				selecting: true,
				selectmode: this.multiple ? "multiple" : "single",
				selected: this.selected,
				sortable: false,
				onPaginate: this.paginate,
				onSelect: this.select
			};
		}
	},
	watch: {
		value(value) {
			this.selected = value;
		}
	},
	methods: {
		paginate({ page }) {
			this.refresh({ page });
		},
		/**
		 * Refresh the dialog with new options
		 * while retaining the payload and selected models
		 */
		refresh(options = {}) {
			this.$panel.dialog.refresh({
				query: {
					...this.payload,
					value: this.selected,
					...options
				}
			});
		},
		search() {
			this.refresh({ search: this.query });
		},
		select(ids) {
			this.selected = ids;
		},
		submit() {
			this.$emit("submit", {
				ids: this.selected,
				items: this.selected
					.map((id) => this.items.find((item) => item.id === id))
					.filter(Boolean)
			});
		}
	}
};
</script>

<style>
.k-model-picker-dialog-header {
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: var(--spacing-2);
	margin-bottom: var(--spacing-3);
}
.k-model-picker-dialog-search {
	flex-grow: 1;
	margin-bottom: 0;
}

.k-model-picker-dialog .k-collection-footer .k-pagination {
	margin-bottom: 0;
}
</style>
