<template>
	<k-dialog
		v-bind="$props"
		class="k-models-dialog"
		@cancel="$emit('cancel')"
		@submit="submit"
	>
		<slot name="header" />

		<k-dialog-search v-if="hasSearch" :value="query" @search="query = $event" />

		<k-collection
			:empty="{
				...empty,
				text: $panel.dialog.isLoading ? $t('loading') : empty.text
			}"
			:items="items"
			:link="false"
			:pagination="{
				details: true,
				dropdown: false,
				align: 'center',
				...pagination
			}"
			:sortable="false"
			layout="list"
			@item="toggle"
			@paginate="paginate"
		>
			<template #options="{ item: row }">
				<k-choice-input
					:checked="isSelected(row)"
					:type="multiple && max !== 1 ? 'checkbox' : 'radio'"
					:title="isSelected(row) ? $t('remove') : $t('select')"
					@click.stop="toggle(row)"
				/>
				<slot name="options" v-bind="{ item: row }" />
			</template>
		</k-collection>
	</k-dialog>
</template>

<script>
import Dialog from "@/mixins/dialog.js";
import Search from "@/mixins/search.js";

export const props = {
	props: {
		endpoint: String,
		empty: Object,
		fetchParams: Object,
		item: {
			type: Function,
			default: (item) => item
		},
		max: Number,
		multiple: {
			type: Boolean,
			default: true
		},
		size: {
			type: String,
			default: "medium"
		},
		value: {
			type: Array,
			default: () => []
		}
	}
};

export default {
	mixins: [Dialog, Search, props],
	emits: ["cancel", "fetched", "submit"],
	data() {
		return {
			models: [],
			selected: this.value.reduce((a, id) => ({ ...a, [id]: { id } }), {}),
			pagination: {
				limit: 20,
				page: 1,
				total: 0
			}
		};
	},
	computed: {
		items() {
			return this.models.map(this.item);
		}
	},
	watch: {
		fetchParams(newParams, oldParams) {
			if (this.$helper.object.same(newParams, oldParams) === false) {
				this.pagination.page = 1;
				this.fetch();
			}
		}
	},
	mounted() {
		this.fetch();
	},
	methods: {
		async fetch() {
			const params = {
				page: this.pagination.page,
				search: this.query,
				...this.fetchParams
			};

			try {
				this.$panel.dialog.isLoading = true;
				const response = await this.$api.get(this.endpoint, params);
				this.models = response.data;
				this.pagination = response.pagination;
				this.$emit("fetched", response);
			} catch (e) {
				this.$panel.error(e);
				this.models = [];
			} finally {
				this.$panel.dialog.isLoading = false;
			}
		},
		isSelected(item) {
			return this.selected[item.id] !== undefined;
		},
		paginate(pagination) {
			this.pagination.page = pagination.page;
			this.pagination.limit = pagination.limit;
			this.fetch();
		},
		submit() {
			this.$emit("submit", Object.values(this.selected));
		},
		async search() {
			this.pagination.page = 0;
			await this.fetch();
		},
		toggle(item) {
			if (this.multiple === false || this.max === 1) {
				this.selected = {};
			}

			if (this.isSelected(item)) {
				return delete this.selected[item.id];
			}

			if (this.max && this.max <= this.$helper.object.length(this.selected)) {
				return;
			}

			this.selected[item.id] = item;
		}
	}
};
</script>

<style>
.k-models-dialog .k-list-item {
	cursor: pointer;
}
.k-models-dialog .k-choice-input {
	--choice-color-checked: var(--color-focus);

	display: flex;
	align-items: center;
	height: var(--item-button-height);
	margin-inline-end: var(--spacing-3);
}
.k-models-dialog .k-choice-input input {
	top: 0;
}

.k-models-dialog .k-collection-footer .k-pagination {
	margin-bottom: 0;
}
</style>
