<template>
	<k-field
		v-bind="$props"
		:class="['k-models-field', `k-${$options.type}-field`, $attrs.class]"
		:style="$attrs.style"
	>
		<template #label>
			<k-label
				:input="false"
				:link="link"
				:required="Boolean(min)"
				:title="label"
				type="field"
			>
				{{ label }}
			</k-label>
		</template>

		<template v-if="buttons" #options>
			<k-button-group
				:buttons="buttons"
				size="xs"
				variant="filled"
				class="k-section-buttons"
			/>
		</template>

		<k-dropzone :disabled="!canDrop" @drop="onDrop">
			<k-input
				v-if="searchable && isSearching"
				:autofocus="true"
				:placeholder="$t('filter') + ' …'"
				:value="searchterm"
				icon="search"
				type="text"
				class="k-models-section-search"
				@input="onSearch"
				@keydown.esc="onSearchToggle"
			/>

			<k-collection
				v-bind="collection"
				v-on="canAdd ? { empty: onAdd } : {}"
				@action="onAction"
				@change="onChange"
				@select="onSelect"
				@sort="onSort"
				@paginate="onPaginate"
			/>
		</k-dropzone>
	</k-field>
</template>

<script>
import { props as FieldProps } from "@/components/Forms/Field.vue";
import { layout } from "@/mixins/props.js";
import batchEditing from "@/mixins/batchEditing";
import debounce from "@/helpers/debounce";

/**
 * @displayName ModelssectionField
 * @since 6.0.0
 */
export default {
	type: "models",
	mixins: [FieldProps, batchEditing, layout],
	inheritAttrs: false,
	props: {
		batch: Boolean,
		columns: Object,
		empty: String,
		fields: Object,
		layout: String,
		link: String,
		max: Number,
		min: Number,
		searchable: Boolean,
		size: String,
		sortable: Boolean
	},
	data() {
		return {
			isSearching: false,
			models: [],
			pagination: {},
			searchterm: null
		};
	},
	computed: {
		addIcon() {
			return "add";
		},
		batchDeleteConfirmMessage() {
			return this.$t(`${this.$options.type}.delete.confirm.selected`, {
				count: this.selected.length
			});
		},
		batchEditingEvent() {
			return "section.selecting";
		},
		buttons() {
			let buttons = [];

			if (this.isSelecting) {
				return this.batchEditingButtons;
			}

			if (this.canSearch) {
				buttons.push({
					icon: "filter",
					text: this.$t("filter"),
					click: () => this.onSearchToggle(),
					responsive: true
				});
			}

			if (this.canSelect) {
				buttons.push(this.batchEditingToggle);
			}

			if (this.canAdd) {
				buttons.push({
					icon: this.addIcon,
					text: this.$t("add"),
					click: () => this.onAdd(),
					responsive: true
				});
			}

			return buttons;
		},
		canAdd() {
			return true;
		},
		canDrop() {
			return false;
		},
		canSearch() {
			return this.searchable;
		},
		canSelect() {
			return this.batch && this.models.length > 0;
		},
		collection() {
			return {
				columns: this.columns,
				empty: this.emptyPropsWithSearch,
				fields: this.fieldsWithColumns,
				layout: this.layout,
				items: this.items,
				pagination: this.pagination,
				selecting: this.isSelecting,
				selected: this.selected,
				sortable: this.sortable && !this.searchterm?.length,
				size: this.size
			};
		},
		emptyProps() {
			return {
				icon: "page",
				text: this.$t("pages.empty")
			};
		},
		emptyPropsWithSearch() {
			return {
				...this.emptyProps,
				text: this.isSearching
					? this.$t("search.results.none")
					: (this.empty ?? this.emptyProps.text)
			};
		},
		fieldsWithColumns() {
			const fields = {};

			for (const field in this.columns ?? {}) {
				fields[field] = {
					...(this.fields?.[field] ?? this.columns[field]),
					disabled: true
				};
			}

			return fields;
		},
		isInvalid() {
			// disable validation while filtering via search
			if (this.searchterm?.length > 0) {
				return false;
			}

			// validate min
			if (this.min && this.items.length < this.min) {
				return true;
			}

			// validate max
			if (this.max && this.items.length > this.max) {
				return true;
			}

			return false;
		},
		items() {
			return this.models;
		},
		paginationId() {
			return "kirby$pagination$" + this.endpoints.field;
		}
	},
	mounted() {
		this.onSearch = debounce(this.onSearch, 200);
		this.load();
		this.$events.on("model.update", this.reload);
	},
	unmounted() {
		this.$events.off("model.update", this.reload);
	},
	methods: {
		async deleteSelected() {
			if (this.selected.length === 0) {
				return;
			}

			try {
				await this.$api.delete(this.endpoints.field + "/delete", {
					ids: this.selected
				});
			} catch (error) {
				this.$panel.notification.error(error);
			} finally {
				this.$panel.events.emit("model.update");
			}
		},
		async load() {
			const page =
				this.pagination.page ??
				sessionStorage.getItem(this.paginationId) ??
				null;

			const response = await this.$api.get(this.endpoints.field + "/models", {
				searchterm: this.searchterm,
				page
			});

			this.models = response.models;
			this.pagination = response.pagination;
		},
		onAction() {},
		onAdd() {},
		onBatchDelete() {
			this.deleteSelected();
		},
		onChange() {},
		onDrop() {},
		onPaginate(pagination) {
			sessionStorage.setItem(this.paginationId, pagination.page);
			this.pagination = pagination;
			this.reload();
		},
		onSearch(query) {
			this.searchterm = query;
			this.pagination.page = 1;
			this.reload();
		},
		onSearchToggle() {
			this.isSearching = !this.isSearching;
			this.searchterm = null;
			this.reload();
		},
		onSort() {},
		async reload() {
			// reset batch mode
			this.stopSelecting();
			await this.load(true);
		}
	}
};
</script>

<style>
.k-models-field-search.k-input {
	--input-color-back: var(--color-border);
	--input-color-border: transparent;
	margin-bottom: var(--spacing-3);
}
</style>
