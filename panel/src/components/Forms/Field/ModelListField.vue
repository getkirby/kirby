<template>
	<k-field
		:id="id"
		:help="help"
		:input="false"
		:invalid="isInvalid"
		:label="label"
		:name="name"
		:required="Boolean(min)"
		:type="type"
		class="k-modellist-field"
	>
		<template v-if="buttons.length > 0" #options>
			<k-button-group :buttons="buttons" size="xs" variant="filled" />
		</template>

		<k-dropzone :disabled="!canDrop" @drop="onDrop">
			<k-input
				v-if="isSearching"
				:autofocus="true"
				:placeholder="$t('filter') + ' …'"
				:value="searchterm"
				class="k-modellist-field-search"
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
				@change="onChange"
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
 * Base for a field that displays list of models
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
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
		 * Layout of the collection
		 * @values list, cardlets, cards, table
		 */
		layout: {
			type: String,
			default: "list"
		},
		/**
		 * Maximum number of entries the list may hold
		 */
		max: Number,
		/**
		 * Minimum number of entries that have to stay in the list
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
		 * Whether the entries can be sorted manually
		 */
		sortable: Boolean
	},
	emits: ["input"],
	data() {
		return {
			isProcessing: false,
			isSearching: false,
			searchterm: null,
			// filled in `created()`, as the initial state is built
			// from computed properties that do not exist yet
			state: {}
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
				buttons.push(this.addButton);
			}

			return buttons;
		},
		addButton() {
			return {
				icon: "add",
				text: this.$t("add"),
				click: () => this.onAdd(),
				responsive: true
			};
		},
		canAdd() {
			return false;
		},
		/**
		 * Only lists that accept file uploads react to a drop
		 */
		canDrop() {
			return false;
		},
		canSelect() {
			return this.batch === true && this.items.length > 0;
		},
		emptyProps() {
			return {
				icon: this.icon,
				text: this.isSearching
					? this.$t("search.results.none")
					: (this.empty ?? this.$t(this.type + ".empty"))
			};
		},
		/**
		 * The icon of the empty state
		 */
		icon() {
			return "box";
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
		/**
		 * The list is not validated while it is filtered,
		 * as the search only shows a part of it
		 */
		isInvalid() {
			if (this.searchterm) {
				return false;
			}

			const total = this.state.pagination.total;

			if (this.min && total < this.min) {
				return true;
			}

			return Boolean(this.max) && total > this.max;
		},
		isSortable() {
			return (
				this.state.sortable === true &&
				this.isSelecting === false &&
				this.isProcessing === false
			);
		},
		items() {
			return this.state.models.map((model) => {
				const sortable = model.permissions.sort && this.isSortable;
				const deletable =
					model.permissions.delete &&
					this.state.pagination.total > (this.min ?? 0);

				return {
					...model,
					data: {
						"data-id": model.id,
						"data-template": model.template
					},
					options: this.$dropdown(model.link, {
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
		 * Each list names its entries after the models it lists
		 */
		models() {
			return [];
		},
		/**
		 * The batch buttons and the empty state build i18n keys from this
		 */
		type() {
			return "models";
		}
	},
	watch: {
		// a new view always brings unfiltered props for the first page,
		// so an active search or page has to be restored through the endpoint
		models() {
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
		this.state = this.stateFromProps();
		this.filter = debounce(this.filter, 200);

		for (const event of this.refreshEvents()) {
			this.$events.on(event, this.onRefresh);
		}
	},
	unmounted() {
		for (const event of this.refreshEvents()) {
			this.$events.off(event, this.onRefresh);
		}
	},
	methods: {
		/**
		 * Debounced in `created()`, so typing does not fire a request per key
		 */
		filter() {
			this.reload({ page: 1 });
		},
		onAction() {},
		onAdd() {},
		async onBatchDelete() {
			await this.request(() =>
				this.$api.delete(this.endpoints.field + "/delete", {
					ids: this.selected
				})
			);
		},
		onChange() {},
		onDrop() {},
		onPaginate(pagination) {
			this.reload({ page: pagination.page });
		},
		/**
		 * `reload()` cannot be used as the listener itself: the event
		 * payload would end up as its query, and a bound arrow function
		 * could not be removed again in `unmounted()`
		 */
		onRefresh() {
			this.reload();
		},
		onSearchToggle() {
			this.isSearching = !this.isSearching;
			this.searchterm = null;
		},
		onSort() {},
		refreshEvents() {
			return ["model.update"];
		},
		/**
		 * Fetches fresh props from the field endpoint, so the list
		 * can update without reloading the whole view
		 */
		async reload(query = {}) {
			this.isProcessing = true;

			try {
				const props = await this.$api.get(this.endpoints.field, {
					page: this.state.pagination.page,
					searchterm: this.searchterm,
					...query
				});

				this.state = { ...props, models: props[this.type] };
			} catch (error) {
				this.$panel.error(error);
			} finally {
				this.isProcessing = false;
			}
		},
		/**
		 * Runs the callback and announces the change afterwards,
		 * which refreshes every list that shows the same models
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
		 * The initial state, in the same shape the endpoint returns
		 */
		stateFromProps() {
			return {
				...this.$props,
				models: this.models
			};
		}
	}
};
</script>

<style>
.k-modellist-field-search.k-input {
	--input-color-back: var(--color-border);
	--input-color-border: transparent;
	margin-bottom: var(--spacing-3);
}
</style>
