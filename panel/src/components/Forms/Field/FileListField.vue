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
				:value="search"
				class="k-filelist-field-search"
				icon="search"
				type="text"
				@input="search = $event"
				@keydown.esc="onSearchToggle"
			/>

			<k-collection
				:columns="columns"
				:empty="emptyProps"
				:fields="fields"
				:items="items"
				:layout="layout"
				:pagination="pagination"
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
		 * The search term the list is currently filtered by
		 */
		searchterm: String,
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
			// the search input is uncommitted state and must not lag
			// behind while typing, so it is kept locally and synced
			// back from the prop whenever the view brings a new one
			isSearching: Boolean(this.searchterm),
			search: this.searchterm
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
			return Boolean(this.upload) && this.$panel.permissions.files.create;
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

			for (const name in this.columns ?? {}) {
				fields[name] = { ...this.columns[name], disabled: true };
			}

			return fields;
		},
		isSortable() {
			return (
				this.sortable === true &&
				this.isSelecting === false &&
				this.isProcessing === false
			);
		},
		items() {
			return this.files.map((file) => {
				const sortable = file.permissions.sort && this.isSortable;
				const deletable =
					file.permissions.delete && this.pagination.total > (this.min ?? 0);

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
				...this.upload,
				url: this.$panel.urls.api + "/" + this.upload.api
			};
		}
	},
	watch: {
		search() {
			this.filter();
		},
		// the view can bring a different term, e.g. on back navigation
		searchterm(searchterm) {
			this.search = searchterm;
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
			// a new term starts over, so the page drops out of the URL again
			this.reload({ page: null });
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
		 * `reload()` cannot be used as the listener itself: the event
		 * payload would end up as its query, and a bound arrow function
		 * could not be removed again in `unmounted()`.
		 *
		 * Concurrent reloads from sibling lists are harmless, as
		 * `view.load()` aborts the previous request. Announcing the
		 * change should still move to the emitters at some point.
		 */
		onRefresh() {
			this.reload();
		},
		onSearchToggle() {
			this.isSearching = !this.isSearching;
			this.search = null;
		},
		async onSort(items) {
			if (this.isSortable === false) {
				return false;
			}

			await this.request(() =>
				this.$api.patch(this.endpoints.field + "/sort", {
					files: items.map((item) => item.id),
					index: this.pagination.offset
				})
			);
		},
		/**
		 * Reloads the view with request parameters scoped to this field,
		 * so that other lists on the same view keep their own page
		 * and search term and only this field gets new props
		 */
		async reload(query = {}) {
			this.isProcessing = true;

			try {
				await this.$panel.view.reload({
					query: {
						fields: {
							[this.name]: {
								page: this.pagination.page,
								// null removes the parameter from the URL
								searchterm: this.search || null,
								...query
							}
						}
					}
				});
			} finally {
				this.isProcessing = false;
			}
		},
		replace(file) {
			this.$panel.upload.replace(file, this.uploadOptions);
		},
		/**
		 * Runs the given callback and afterwards announces the change,
		 * which reloads the view and with it every list that shows
		 * the same files
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
