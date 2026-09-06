<template>
	<k-field
		:id="id"
		:help="help"
		:input="false"
		:label="label"
		:link="link"
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

			<k-validator v-bind="validator">
				<k-collection
					:columns="state.columns"
					:empty="emptyProps"
					:fields="fields"
					:items="isLoading ? skeleton : items"
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
			</k-validator>
		</k-dropzone>
	</k-field>
</template>

<script>
import batchEditing from "@/mixins/batchEditing";
import debounce from "@/helpers/debounce";
import { help, id, label, name, type } from "@/mixins/props.js";

/**
 * Base for a field that displays list of models
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
export default {
	mixins: [help, id, label, name, type, batchEditing],
	inheritAttrs: false,
	type: "models",
	props: {
		/**
		 * Shows the batch select interface
		 */
		batch: Boolean,
		columns: {
			type: [Array, Object],
			default: () => ({})
		},
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
		 * Link to the parent the model entries come from,
		 * unless that is the view's model itself
		 */
		link: String,
		/**
		 * Maximum number of entries the list may hold
		 */
		max: Number,
		/**
		 * Minimum number of entries that have to stay in the list
		 */
		min: Number,
		/**
		 * Shows the search button
		 */
		searchable: Boolean,
		/**
		 * Card size for `layout: cards`
		 */
		size: String
	},
	data() {
		return {
			error: null,
			isLoading: true,
			isSearching: false,
			searchterm: null,
			state: {
				columns: this.columns,
				models: [],
				pagination: { limit: 0, offset: 0, page: 1, total: 0 },
				sortable: false
			}
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
		canDrop() {
			return false;
		},
		canSelect() {
			return this.batch === true && this.items.length > 0;
		},
		emptyProps() {
			return {
				icon: this.error !== null ? "alert" : this.icon,
				text: this.emptyText
			};
		},
		emptyText() {
			if (this.isLoading === true) {
				return this.$t("loading");
			}

			if (this.error !== null) {
				return this.error;
			}

			if (this.isSearching === true) {
				return this.$t("search.results.none");
			}

			return this.empty ?? this.$t(this.$options.type + ".empty");
		},
		/**
		 * The icon of the empty state
		 */
		icon() {
			return "box";
		},
		fields() {
			const fields = {};

			for (const name in this.state.columns ?? {}) {
				// table cells must never be editable
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
		skeleton() {
			return [
				{
					id: "skeleton",
					image: this.layout !== "table" ? { icon: "loader" } : null,
					theme: "skeleton"
				}
			];
		},
		/**
		 * The list is only validated while it shows everything.
		 * A search narrows it down to a part of the collection
		 * and the loading list knows no entries at all.
		 */
		validator() {
			const count = this.state.pagination.total;

			if (this.isLoading === true || this.searchterm) {
				return { count };
			}

			return { count, max: this.max, min: this.min };
		}
	},
	watch: {
		searchterm() {
			this.filter();
		}
	},
	created() {
		this.filter = debounce(this.filter, 200);
		this.onRefresh = debounce(this.onRefresh, 0);

		for (const event of this.refreshEvents()) {
			this.$events.on(event, this.onRefresh);
		}
	},
	mounted() {
		this.reload();
	},
	unmounted() {
		for (const event of this.refreshEvents()) {
			this.$events.off(event, this.onRefresh);
		}
	},
	methods: {
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
		onRefresh() {
			this.reload();
		},
		onSearchToggle() {
			this.isSearching = !this.isSearching;
			this.searchterm = null;
		},
		onSort() {},
		/**
		 * Events after which the list has to load its entries
		 * again. Every action that changes what the list shows
		 * has to be in here, as the view no longer carries the
		 * entries along when it reloads.
		 */
		refreshEvents() {
			return ["model.update"];
		},
		/**
		 * Fetches a fresh state from the field endpoint,
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
				this.error = null;
			} catch (error) {
				this.error = error.message;
				this.$panel.error(error);
			} finally {
				this.isLoading = false;
				this.isProcessing = false;
			}

			await this.$nextTick();
			this.$events.emit("field.loaded", this);
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
