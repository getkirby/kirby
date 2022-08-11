<template>
	<section
		v-if="isLoading === false"
		:data-processing="isProcessing"
		:class="`k-models-section k-${type}-section`"
	>
		<header class="k-section-header">
			<k-headline :link="options.link">
				{{ options.headline || " " }}
				<abbr v-if="options.min" :title="$t('section.required')">*</abbr>
			</k-headline>

			<k-button-group :buttons="buttons" />
		</header>

		<!-- Error -->
		<k-box v-if="error" theme="negative">
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
					v-model="searchterm"
					:autofocus="true"
					:placeholder="$t('search') + ' …'"
					type="text"
					class="k-models-section-search"
					@keydown.esc="onSearchToggle"
				/>

				<!-- Models collection -->
				<k-collection
					v-bind="collection"
					:data-invalid="isInvalid"
					v-on="canAdd ? { empty: onAdd } : {}"
					@action="onAction"
					@change="onChange"
					@sort="onSort"
					@paginate="onPaginate"
				/>
			</k-dropzone>

			<k-upload ref="upload" @success="onUpload" @error="reload" />
		</template>
	</section>
</template>

<script>
import debounce from "@/helpers/debounce";

export default {
	inheritAttrs: false,
	props: {
		blueprint: String,
		column: String,
		parent: String,
		name: String,
		timestamp: Number
	},
	data() {
		return {
			data: [],
			error: null,
			isLoading: false,
			isProcessing: false,
			options: {
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
		buttons() {
			let buttons = [];

			if (this.canSearch) {
				buttons.push({
					icon: "filter",
					text: this.$t("search"),
					click: this.onSearchToggle,
					responsive: true
				});
			}

			if (this.canAdd) {
				buttons.push({
					icon: this.addIcon,
					text: this.$t("add"),
					click: this.onAdd
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
		collection() {
			return {
				columns: this.options.columns,
				empty: this.emptyPropsWithSearch,
				layout: this.options.layout,
				help: this.options.help,
				items: this.items,
				pagination: this.pagination,
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
					: this.options.empty || this.emptyProps.text
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
		searchterm: debounce(function () {
			this.pagination.page = 0;
			this.reload();
		}, 200),
		// Reload the section when
		// the view has changed in the backend
		timestamp() {
			this.reload();
		}
	},
	created() {
		this.load();
	},
	methods: {
		async load(reload) {
			if (!reload) {
				this.isLoading = true;
			}

			this.isProcessing = true;

			if (this.pagination.page === null) {
				this.pagination.page = localStorage.getItem(this.paginationId) || 1;
			}

			try {
				const response = await this.$api.get(
					this.parent + "/sections/" + this.name,
					{ page: this.pagination.page, searchterm: this.searchterm }
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
		onChange() {},
		onDrop() {},
		onSort() {},
		onPaginate(pagination) {
			localStorage.setItem(this.paginationId, pagination.page);
			this.pagination = pagination;
			this.reload();
		},
		onSearchToggle() {
			this.searching = !this.searching;
			this.searchterm = null;
		},
		onUpload() {},

		async reload() {
			await this.load(true);
		},
		update() {
			this.reload();
			this.$events.$emit("model.update");
		}
	}
};
</script>

<style>
.k-models-section[data-processing="true"] {
	pointer-events: none;
}

.k-models-section-search.k-input {
	margin-bottom: var(--spacing-3);
	background: var(--color-gray-300);
	padding: var(--spacing-2) var(--spacing-3);
	height: var(--field-input-height);
	border-radius: var(--rounded);
	font-size: var(--text-sm);
}
</style>
