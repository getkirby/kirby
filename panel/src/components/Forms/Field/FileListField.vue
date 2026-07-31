<template>
	<k-field
		:id="id"
		:help="help"
		:input="false"
		:label="label"
		:name="name"
		class="k-filelist-field"
		type="filelist"
	>
		<template v-if="buttons.length > 0" #options>
			<k-button-group :buttons="buttons" size="xs" variant="filled" />
		</template>

		<k-dropzone :disabled="!canAdd" @drop="onDrop">
			<k-input
				v-if="isSearching"
				:autofocus="true"
				:placeholder="$t('filter') + ' …'"
				:value="searchterm"
				class="k-filelist-field-search"
				icon="search"
				type="text"
				@input="searchterm = $event"
				@keydown.esc="onSearchToggle"
			/>

			<k-collection
				:columns="state.columns"
				:empty="emptyProps"
				:fields="fields"
				:items="items"
				:layout="layout"
				:pagination="state.pagination"
				:selected="selected"
				:selecting="isSelecting"
				:size="size"
				:sortable="isSortable"
				v-on="canAdd ? { empty: onAdd } : {}"
				@action="onAction"
				@paginate="onPaginate"
				@select="onSelect"
				@sort="onSort"
			/>
		</k-dropzone>
	</k-field>
</template>

<script>
import batchEditing from "@/mixins/batchEditing";
import debounce from "@/helpers/debounce";
import { help, id, label, name } from "@/mixins/props.js";

/**
 * Lists the files of a model
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
export default {
	mixins: [help, id, label, name, batchEditing],
	inheritAttrs: false,
	props: {
		/**
		 * Shows the batch select interface
		 */
		batch: Boolean,
		/**
		 * Column definitions for `layout: table`
		 */
		columns: Object,
		/**
		 * Text for the empty state box
		 */
		empty: String,
		endpoints: Object,
		/**
		 * Props for each file of the current page
		 */
		files: {
			type: Array,
			default: () => []
		},
		/**
		 * Layout of the collection
		 * @values list, cardlets, cards, table
		 */
		layout: {
			type: String,
			default: "list"
		},
		/**
		 * Minimum number of files that have to stay in the list
		 */
		min: Number,
		/**
		 * Pagination info for the current page
		 */
		pagination: Object,
		/**
		 * Shows the search button
		 */
		searchable: Boolean,
		/**
		 * Card size for `layout: cards`
		 */
		size: String,
		/**
		 * Whether the files can be sorted manually
		 */
		sortable: Boolean,
		/**
		 * Upload settings or `false` if uploads are disabled
		 */
		upload: {
			type: [Boolean, Object],
			default: false
		}
	},
	emits: ["input"],
	data() {
		return {
			isProcessing: false,
			isSearching: false,
			searchterm: null,
			state: this.stateFromProps()
		};
	},
	computed: {
		buttons() {
			if (this.isSelecting === true) {
				return this.batchEditingButtons;
			}

			const buttons = [];

			if (this.searchable === true) {
				buttons.push({
					icon: "filter",
					text: this.$t("filter"),
					click: () => this.onSearchToggle(),
					responsive: true
				});
			}

			if (this.canSelect === true) {
				buttons.push(this.batchEditingToggle);
			}

			if (this.canAdd === true) {
				buttons.push({
					icon: "upload",
					text: this.$t("add"),
					click: () => this.onAdd(),
					responsive: true
				});
			}

			return buttons;
		},
		canAdd() {
			return Boolean(this.state.upload) && this.$panel.permissions.files.create;
		},
		canSelect() {
			return this.batch === true && this.items.length > 0;
		},
		emptyProps() {
			return {
				icon: "image",
				text: this.isSearching
					? this.$t("search.results.none")
					: (this.empty ?? this.$t("files.empty"))
			};
		},
		/**
		 * Table cells must never be editable
		 */
		fields() {
			const fields = {};

			for (const name in this.state.columns ?? {}) {
				fields[name] = { ...this.state.columns[name], disabled: true };
			}

			return fields;
		},
		isSortable() {
			return (
				this.state.sortable === true &&
				this.isSelecting === false &&
				this.isProcessing === false
			);
		},
		items() {
			return this.state.files.map((file) => {
				const sortable = file.permissions.sort && this.isSortable;
				const deletable =
					file.permissions.delete &&
					this.state.pagination.total > (this.min ?? 0);

				return {
					...file,
					data: {
						"data-id": file.id,
						"data-template": file.template
					},
					options: this.$dropdown(file.link, {
						query: {
							delete: deletable,
							sort: sortable,
							view: "list"
						}
					}),
					selectable: this.isSelecting && deletable,
					sortable: sortable
				};
			});
		},
		/**
		 * The batch editing mixin builds its i18n keys from this
		 */
		type() {
			return "files";
		},
		uploadOptions() {
			return {
				...this.state.upload,
				url: this.$panel.urls.api + "/" + this.state.upload.api
			};
		}
	},
	watch: {
		// a new view always brings unfiltered props for the first page,
		// so an active search or page has to be restored through the endpoint
		files() {
			if (this.searchterm || this.state.pagination.page > 1) {
				this.reload();
			} else {
				this.state = this.stateFromProps();
			}
		},
		searchterm() {
			this.filter();
		}
	},
	created() {
		this.filter = debounce(this.filter, 200);

		// uploads and deletions happen outside of this component
		this.$events.on("file.sort", this.onRefresh);
		this.$events.on("model.update", this.onRefresh);
	},
	unmounted() {
		this.$events.off("file.sort", this.onRefresh);
		this.$events.off("model.update", this.onRefresh);
	},
	methods: {
		/**
		 * Debounced in `created()`, so typing does not
		 * fire a request on every keystroke
		 */
		filter() {
			this.reload({ page: 1 });
		},
		onAction(action, file) {
			if (action === "replace") {
				this.replace(file);
			}
		},
		onAdd() {
			if (this.canAdd === true) {
				this.$panel.upload.pick(this.uploadOptions);
			}
		},
		async onBatchDelete() {
			await this.request(() =>
				this.$api.delete(this.endpoints.field + "/delete", {
					ids: this.selected
				})
			);
		},
		onDrop(files) {
			if (this.canAdd === true) {
				this.$panel.upload.open(files, this.uploadOptions);
			}
		},
		onPaginate(pagination) {
			this.reload({ page: pagination.page });
		},
		/**
		 * The event payload must not be passed on as a query
		 */
		onRefresh() {
			this.reload();
		},
		onSearchToggle() {
			this.isSearching = !this.isSearching;
			this.searchterm = null;
		},
		async onSort(items) {
			if (this.isSortable === false) {
				return false;
			}

			await this.request(() =>
				this.$api.patch(this.endpoints.field + "/sort", {
					files: items.map((item) => item.id),
					index: this.state.pagination.offset
				})
			);
		},
		/**
		 * Fetches a fresh set of props from the field endpoint,
		 * so the list can update without reloading the whole view
		 */
		async reload(query = {}) {
			this.isProcessing = true;

			try {
				this.state = await this.$api.get(this.endpoints.field, {
					page: this.state.pagination.page,
					searchterm: this.searchterm,
					...query
				});
			} catch (error) {
				this.$panel.error(error);
			} finally {
				this.isProcessing = false;
			}
		},
		replace(file) {
			this.$panel.upload.replace(file, this.uploadOptions);
		},
		/**
		 * Runs a write request and refreshes every list on the page,
		 * as they can show the same files
		 */
		async request(callback) {
			this.isProcessing = true;

			try {
				await callback();
				this.$panel.notification.success();
				this.stopSelecting();
			} catch (error) {
				this.$panel.error(error);
			} finally {
				this.$panel.events.emit("model.update");
				this.isProcessing = false;
			}
		},
		/**
		 * Everything that the endpoint can replace on a refresh
		 */
		stateFromProps() {
			return {
				columns: this.columns,
				files: this.files,
				pagination: this.pagination,
				sortable: this.sortable,
				upload: this.upload
			};
		}
	}
};
</script>

<style>
.k-filelist-field-search.k-input {
	--input-color-back: var(--color-border);
	--input-color-border: transparent;
	margin-bottom: var(--spacing-3);
}
</style>
