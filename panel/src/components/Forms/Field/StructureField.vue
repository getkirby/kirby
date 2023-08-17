<template>
	<k-field v-bind="$props" class="k-structure-field" @click.native.stop>
		<template v-if="hasFields && !disabled" #options>
			<k-dropdown>
				<k-button
					icon="dots"
					size="xs"
					variant="filled"
					@click="$refs.options.toggle()"
				/>
				<k-dropdown-content ref="options" align-x="end">
					<k-dropdown-item :disabled="!more" icon="add" @click="add()">
						{{ $t("add") }}
					</k-dropdown-item>
					<k-dropdown-item
						:disabled="items.length === 0 || disabled"
						icon="trash"
						@click="removeAll"
					>
						{{ $t("delete.all") }}
					</k-dropdown-item>
				</k-dropdown-content>
			</k-dropdown>
		</template>

		<template v-if="hasFields">
			<!-- Empty State -->
			<k-empty
				v-if="items.length === 0"
				:data-invalid="isInvalid"
				icon="list-bullet"
				@click="add()"
			>
				{{ empty ?? $t("field.structure.empty") }}
			</k-empty>

			<!-- Table -->
			<template v-else>
				<k-table
					:columns="columns"
					:disabled="disabled"
					:fields="fields"
					:empty="$t('field.structure.empty')"
					:index="index"
					:options="options"
					:pagination="limit ? pagination : false"
					:rows="paginatedItems"
					:sortable="isSortable"
					:data-invalid="isInvalid"
					@cell="jump($event.rowIndex, $event.columnIndex)"
					@input="save"
					@option="option"
					@paginate="paginate"
				/>

				<footer v-if="more" class="k-bar" data-align="center">
					<k-button
						:title="$t('add')"
						icon="add"
						size="xs"
						variant="filled"
						@click="add()"
					/>
				</footer>
			</template>
		</template>
		<template v-else>
			<k-empty icon="list-bullet">{{ $t("fields.empty") }}</k-empty>
		</template>
	</k-field>
</template>

<script>
import { props as Field } from "@/components/Forms/Field.vue";

export default {
	mixins: [Field],
	inheritAttrs: false,
	props: {
		/**
		 * What columns to show in the table
		 */
		columns: Object,
		/**
		 * Whether to allow row duplication
		 */
		duplicate: {
			type: Boolean,
			default: true
		},
		/**
		 * The text, that is shown when the field has no entries.
		 */
		empty: String,
		/**
		 * Fields for the form
		 */
		fields: [Array, Object],
		/**
		 * How many rows to show per page
		 */
		limit: Number,
		/**
		 * Upper limit of rows allowed
		 */
		max: Number,
		/**
		 * Lower limit of rows required
		 */
		min: Number,
		/**
		 * Whether to insert new entries at the top
		 * of the list instead at the end
		 */
		prepend: {
			type: Boolean,
			default: false
		},
		/**
		 * Whether to allow sorting of rows
		 */
		sortable: {
			type: Boolean,
			default: true
		},
		/**
		 * Expression by which to sort rows automatically
		 */
		sortBy: String,
		value: {
			type: Array,
			default: () => []
		}
	},
	data() {
		return {
			autofocus: null,
			items: this.toItems(this.value),
			page: 1
		};
	},
	computed: {
		/**
		 * Config options for `k-draggable`
		 * @returns {Object}
		 */
		dragOptions() {
			return {
				disabled: !this.isSortable,
				fallbackClass: "k-sortable-row-fallback"
			};
		},
		/**
		 * Config for the structure form
		 * @returns {Object}
		 */
		form() {
			return this.$helper.field.subfields(this, this.fields);
		},
		/**
		 * Index of first row that is displayed
		 * @returns {number}
		 */
		index() {
			if (!this.limit) {
				return 1;
			}

			return (this.page - 1) * this.limit + 1;
		},
		/**
		 * Returns if new entries can be added
		 * @returns {bool}
		 */
		more() {
			if (this.disabled === true) {
				return false;
			}

			if (this.max && this.items.length >= this.max) {
				return false;
			}

			return true;
		},
		hasFields() {
			return this.$helper.object.length(this.fields) > 0;
		},
		/**
		 * Returns if field is invalid
		 * @returns {bool}
		 */
		isInvalid() {
			if (this.disabled === true) {
				return false;
			}

			if (this.min && this.items.length < this.min) {
				return true;
			}

			if (this.max && this.items.length > this.max) {
				return true;
			}

			return false;
		},
		/**
		 * Returns whether the rows can be sorted
		 * @returns {bool}
		 */
		isSortable() {
			if (this.sortBy) {
				return false;
			}

			if (this.limit) {
				return false;
			}

			if (this.disabled === true) {
				return false;
			}

			if (this.items.length <= 1) {
				return false;
			}

			if (this.sortable === false) {
				return false;
			}

			return true;
		},
		/**
		 * Returns config for `k-pagination`
		 * @returns {Obect}
		 */
		pagination() {
			let offset = 0;

			if (this.limit) {
				offset = (this.page - 1) * this.limit;
			}

			return {
				page: this.page,
				offset: offset,
				limit: this.limit,
				total: this.items.length,
				align: "center",
				details: true
			};
		},
		/**
		 * Returns array of options for dropdown in rows
		 * @returns {Array}
		 */
		options() {
			if (this.disabled) {
				return [];
			}

			let options = [];
			let more = this.duplicate && this.more;

			options.push({
				icon: "edit",
				text: this.$t("edit"),
				click: "edit"
			});

			options.push({
				disabled: !more,
				icon: "copy",
				text: this.$t("duplicate"),
				click: "duplicate"
			});

			options.push("-");

			options.push({
				icon: "trash",
				text: more ? this.$t("delete") : null,
				click: "remove"
			});

			return options;
		},
		/**
		 * Returns paginated slice of items/rows
		 * @returns {Array}
		 */
		paginatedItems() {
			if (!this.limit) {
				return this.items;
			}

			return this.items.slice(
				this.pagination.offset,
				this.pagination.offset + this.limit
			);
		}
	},
	watch: {
		value(value) {
			if (value !== this.items) {
				this.items = this.toItems(value);
			}
		}
	},
	methods: {
		/**
		 * Adds new entry
		 * @public
		 */
		add(value = null) {
			if (this.more === false) {
				return false;
			}

			value = value ?? this.$helper.field.form(this.fields);

			let index = 0;

			if (this.prepend === true) {
				this.items.unshift(value);
				index = 0;
			} else {
				this.items.push(value);
				index = this.items.length - 1;
			}

			this.save();
			this.open(index);
		},
		close() {
			this.$panel.drawer.close(this._uid);
		},

		/**
		 * Focuses the add button
		 * @public
		 */
		focus() {
			this.$refs.add?.focus?.();
		},
		/**
		 * Opens form for a specific row at index
		 * with field focussed
		 * @param {number} index
		 * @param {string} field
		 */
		jump(index, field) {
			this.open(index + this.pagination.offset, field);
		},

		/**
		 * Edit the structure field entry at `index` position
		 * in the structure form with field `field` focused
		 * @public
		 * @param {number} index
		 * @param {string} field
		 */
		open(index, field, replace = false) {
			if (this.disabled === true || !this.items[index]) {
				return false;
			}

			this.$panel.drawer.open({
				component: "k-structure-drawer",
				id: this._uid,
				props: {
					icon: this.icon ?? "list-bullet",
					next: this.items[index + 1],
					prev: this.items[index - 1],
					tabs: {
						content: {
							fields: this.form
						}
					},
					title: this.label,
					value: this.items[index]
				},
				replace: replace,
				on: {
					input: (value) => {
						this.$set(this.items, index, value);
						this.save();
					},
					next: () => {
						this.open(index + 1, null, true);
					},
					prev: () => {
						this.open(index - 1, null, true);
					},
					remove: () => {
						this.remove(index);
					}
				}
			});
		},

		/**
		 * Called when option from row's dropdown was engaged
		 * @param {string} option option name that was triggered
		 * @param {Object} row
		 * @param {number} rowIndex
		 */
		option(option, row, rowIndex) {
			switch (option) {
				case "remove":
					this.remove(rowIndex + this.pagination.offset);
					break;

				case "duplicate":
					this.add(this.items[rowIndex + this.pagination.offset]);
					break;

				case "edit":
					this.open(rowIndex);
					break;
			}
		},

		/**
		 * Update pagination state
		 * @param {Object} pagination
		 */
		paginate({ page }) {
			this.page = page;
		},
		/**
		 * Remove current entry
		 */
		remove(index) {
			if (this.disabled || index === null) {
				return;
			}

			this.$panel.dialog.open({
				component: "k-remove-dialog",
				props: {
					text: this.$t("field.structure.delete.confirm")
				},
				on: {
					submit: () => {
						this.items.splice(index, 1);
						this.save();
						this.$panel.dialog.close();
						this.close();

						// if pagination page doesn't exist anymore,
						// go to previous page
						if (this.paginatedItems.length === 0 && this.page > 1) {
							this.page--;
						}

						this.items = this.sort(this.items);
					}
				}
			});
		},
		/**
		 * Remove all entries
		 */
		removeAll() {
			this.$panel.dialog.open({
				component: "k-remove-dialog",
				props: {
					text: this.$t("field.structure.delete.confirm.all")
				},
				on: {
					submit: () => {
						this.page = 1;
						this.items = [];
						this.save();
						this.$panel.dialog.close();
					}
				}
			});
		},
		/**
		 * When the field's value changes
		 * @param {array} values
		 */
		save(values = this.items) {
			this.$emit("input", values);
		},
		/**
		 * Sort items according to `sortBy` prop
		 * @param {Array} items
		 * @returns {Array}
		 */
		sort(items) {
			if (!this.sortBy) {
				return items;
			}

			return items.sortBy(this.sortBy);
		},
		/**
		 * Converts field value to internal
		 * items state
		 * @param {Array} value
		 * @returns {Array}
		 */
		toItems(value) {
			if (Array.isArray(value) === false) {
				return [];
			}

			return this.sort(value);
		}
	}
};
</script>

<style>
.k-structure-field:not([data-disabled="true"]) td.k-table-column {
	cursor: pointer;
}
/** .k-structure-field .k-table:has(+ footer) */
.k-structure-field .k-table + footer {
	margin-top: var(--spacing-3);
}
</style>
