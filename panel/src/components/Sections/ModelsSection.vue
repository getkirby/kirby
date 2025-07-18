<template>
	<k-section
		v-if="isLoading === false"
		:buttons="buttons"
		:class="['k-models-section', `k-${type}-section`, $attrs.class]"
		:data-processing="isProcessing"
		:headline="options.headline ?? ' '"
		:invalid="isInvalid"
		:link="options.link"
		:required="Boolean(options.min)"
		:style="$attrs.style"
	>
		<!-- Error -->
		<k-box v-if="error" icon="alert" theme="negative">
			<k-text size="small">
				<strong> {{ $t("error.section.notLoaded", { name: name }) }}: </strong>
				{{ error }}
			</k-text>
		</k-box>

		<template v-else>
			<k-dropzone :disabled="!canDrop" @drop="onDrop">
				<!-- Search filter  -->
				<k-input
					v-if="searching && options.search"
					:autofocus="true"
					:placeholder="$t('filter') + ' …'"
					:value="searchterm"
					icon="search"
					type="text"
					class="k-models-section-search"
					@input="searchterm = $event"
					@keydown.native.esc="onSearchToggle"
				/>

				<!-- Models collection -->
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
		</template>
	</k-section>
</template>

<script>
import debounce from "@/helpers/debounce";
import section from "@/mixins/section";
import batchEditing from "@/mixins/batchEditing";

export default {
	mixins: [section, batchEditing],
	inheritAttrs: false,
	props: {
		column: String
	},
	data() {
		return {
			data: [],
			error: null,
			isLoading: false,
			isProcessing: false,
			options: {
				batch: false,
				columns: {},
				empty: null,
				headline: null,
				help: null,
				layout: "list",
				link: null,
				max: null,
				min: null,
				size: null,
				sortable: null
			},
			pagination: {
				page: null
			},
			searchterm: null,
			searching: false
		};
	},
	computed: {
		addIcon() {
			return "add";
		},
		batchDeleteConfirmMessage() {
			return this.$t(`${this.type}.delete.confirm.selected`, {
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
					click: this.onSearchToggle,
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
					click: this.onAdd,
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
			return this.options.search;
		},
		canSelect() {
			return this.options.batch && this.items.length > 0;
		},
		collection() {
			return {
				columns: this.options.columns,
				empty: this.emptyPropsWithSearch,
				fields: this.options.fields,
				layout: this.options.layout,
				help: this.options.help,
				items: this.items,
				pagination: this.pagination,
				selecting: !this.isProcessing && this.isSelecting,
				sortable: !this.isProcessing && this.options.sortable,
				size: this.options.size
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
				text: this.searching
					? this.$t("search.results.none")
					: (this.options.empty ?? this.emptyProps.text)
			};
		},
		items() {
			return this.data;
		},
		isInvalid() {
			// disable validation while filtering via search
			if (this.searchterm?.length > 0) {
				return false;
			}

			// validate min
			if (this.options.min && this.data.length < this.options.min) {
				return true;
			}

			// validate max
			if (this.options.max && this.data.length > this.options.max) {
				return true;
			}

			return false;
		},
		paginationId() {
			return "kirby$pagination$" + this.parent + "/" + this.name;
		},
		type() {
			return "models";
		}
	},
	watch: {
		searchterm() {
			this.search();
		},
		// Reload the section when
		// the view has changed in the backend
		timestamp() {
			this.reload();
		}
	},
	created() {
		this.$events.on("model.update", this.reload);
	},
	destroyed() {
		this.$events.off("model.update", this.reload);
	},
	mounted() {
		this.search = debounce(this.search, 200);
		this.load();
	},
	methods: {
		async deleteSelected() {
			if (this.selected.length === 0) {
				return;
			}

			this.isProcessing = true;

			try {
				await this.$api.delete(
					this.parent + "/sections/" + this.name + "/delete",
					{
						ids: this.selected.map((item) => item.id)
					}
				);
			} catch (error) {
				this.$panel.notification.error(error);
			} finally {
				this.$panel.events.emit("model.update");
				this.isProcessing = false;
			}
		},
		async load(reload) {
			this.isProcessing = true;

			if (!reload) {
				this.isLoading = true;
			}

			const page =
				this.pagination.page ??
				sessionStorage.getItem(this.paginationId) ??
				null;

			try {
				const response = await this.$api.get(
					this.parent + "/sections/" + this.name,
					{ page, searchterm: this.searchterm }
				);

				this.options = response.options;
				this.pagination = response.pagination;
				this.data = response.data;
			} catch (error) {
				this.error = error.message;
			} finally {
				this.isProcessing = false;
				this.isLoading = false;
			}
		},
		onAction() {},
		onAdd() {},
		onBatchDelete() {
			this.deleteSelected();
		},
		onChange() {},
		onDrop() {},
		onPaginate(pagination) {
			// update pagination page
			sessionStorage.setItem(this.paginationId, pagination.page);
			this.pagination = pagination;
			this.reload();
		},
		onSearchToggle() {
			this.searching = !this.searching;
			this.searchterm = null;
		},
		onSort() {},
		async reload() {
			// reset batch mode
			this.stopSelecting();
			await this.load(true);
		},
		async search() {
			this.pagination.page = 0;
			await this.reload();
		},
		update() {
			this.reload();
			this.$events.emit("model.update");
		}
	}
};
</script>

<style>
.k-models-section[data-processing="true"] {
	pointer-events: none;
}

.k-models-section-search.k-input {
	--input-color-back: var(--color-border);
	--input-color-border: transparent;
	margin-bottom: var(--spacing-3);
}
</style>
