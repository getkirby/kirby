<template>
	<k-field v-bind="$props" class="k-structure-field" @click.native.stop>
		<template #options>
			<k-dropdown>
				<k-button
					:disabled="currentIndex !== null"
					icon="dots"
					@click="$refs.options.toggle()"
				/>
				<k-dropdown-content ref="options" align="right">
					<k-dropdown-item :disabled="!more" icon="add" @click="onAdd">
						{{ $t("add") }}
					</k-dropdown-item>
					<k-dropdown-item
						:disabled="items.length === 0 || disabled"
						icon="trash"
						@click="confirmToRemoveAll"
					>
						{{ $t("delete.all") }}
					</k-dropdown-item>
				</k-dropdown-content>
			</k-dropdown>
		</template>

		<!-- Form -->
		<k-structure-form
			v-if="currentIndex !== null"
			ref="form"
			:fields="form"
			:index="currentIndex"
			:total="items.length"
			:value="currentModel"
			@close="onFormClose"
			@discard="onFormDiscard"
			@input="onFormInput"
			@paginate="onFormPaginate($event.offset)"
			@submit="onFormSubmit"
		/>

		<!-- Empty State -->
		<k-empty
			v-else-if="items.length === 0"
			:data-invalid="isInvalid"
			icon="list-bullet"
			@click="onAdd"
		>
			{{ empty || $t("field.structure.empty") }}
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
				@input="onInput"
				@option="onOption"
				@paginate="paginate"
			/>

			<k-button
				v-if="more"
				class="k-field-add-item-button"
				icon="add"
				:tooltip="$t('add')"
				@click="onAdd"
			/>

			<k-remove-dialog
				v-if="!disabled"
				ref="remove"
				theme="negative"
				:submit-button="$t('delete')"
				:text="$t('field.structure.delete.confirm')"
				@submit="onRemove"
			/>

			<k-remove-dialog
				ref="dialogRemoveAll"
				:text="$t('field.structure.delete.confirm.all')"
				@submit="onRemoveAll"
			/>
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
		fields: Object,
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
			default() {
				return [];
			}
		}
	},
	data() {
		return {
			autofocus: null,
			items: this.toItems(this.value),
			currentIndex: null,
			currentModel: null,
			trash: null,
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
			let more = this.duplicate && this.more && this.currentIndex === null;

			options.push({
				icon: "edit",
				text: this.$t("edit"),
				click: "edit"
			});

			if (more) {
				options.push({
					icon: "copy",
					text: this.$t("duplicate"),
					click: "duplicate"
				});
			}

			options.push({
				icon: "remove",
				text: more ? this.$t("remove") : null,
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
			if (value != this.items) {
				this.items = this.toItems(value);
			}
		}
	},
	methods: {
		/**
		 * Adds new entry
		 * @public
		 * @param {Object} value object with values for each field
		 */
		add(value) {
			if (this.prepend === true) {
				this.items.unshift(value);
			} else {
				this.items.push(value);
			}
		},
		confirmToRemoveAll() {
			this.$refs.dialogRemoveAll.open();
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
		 * Called when adding new structure entry
		 */
		onAdd() {
			// ignore if field is disabled
			if (this.disabled === true) {
				return false;
			}

			// if form is already open, discard it (if possible)
			if (this.currentIndex !== null) {
				this.onFormDiscard();
				return false;
			}

			this.currentIndex = "new";
			this.currentModel = this.$helper.field.form(this.fields);

			this.onFormOpen();
		},
		/**
		 * Handles the closing of the structure form
		 */
		onFormClose() {
			this.currentIndex = null;
			this.currentModel = null;
		},
		/**
		 * Handles when the structure form is discarded (e.g. by escape key)
		 */
		onFormDiscard() {
			// when adding a new item, make sure to only discard empty form
			if (this.currentIndex === "new") {
				const values = Object.values(this.currentModel).filter(
					(value) => this.$helper.object.isEmpty(value) === false
				);

				if (values.length === 0) {
					this.onFormClose();
					return;
				}
			}

			this.onFormSubmit();
		},
		/**
		 * Handles the creation and opening of the structure form
		 * @param {string} field form field to focus
		 */
		onFormOpen(field = this.autofocus) {
			this.$nextTick(() => {
				this.$refs.form?.focus(field);
			});
		},
		/**
		 * Called when pagination changes in open form
		 * @param {number} index index of new row to be shown
		 */
		async onFormPaginate(index) {
			try {
				await this.save();
				this.open(index);
			} catch (e) {
				// don't change the page
			}
		},
		/**
		 * Handles the structure form submission
		 */
		async onFormSubmit() {
			try {
				await this.save();
				this.onFormClose();
			} catch (e) {
				// don't close
			}
		},
		/**
		 * When the field's value changes
		 * @param {array} values
		 */
		onInput(values = this.items) {
			this.$emit("input", values);
		},
		/**
		 * Called when option from row's dropdown was engaged
		 * @param {string} option option name that was triggered
		 * @param {Object} row
		 * @param {number} rowIndex
		 */
		onOption(option, row, rowIndex) {
			switch (option) {
				case "remove":
					this.onFormClose();
					this.trash = rowIndex + this.pagination.offset;
					this.$refs.remove.open();
					break;

				case "duplicate":
					this.add(this.items[rowIndex + this.pagination.offset]);
					this.onInput();
					break;

				case "edit":
					this.open(rowIndex);
					break;
			}
		},
		/**
		 * When removal has been confirmed,
		 * remove entry
		 */
		onRemove() {
			// stop if no entry has been flagged for removal
			if (this.trash === null) {
				return false;
			}

			this.items.splice(this.trash, 1);
			this.trash = null;
			this.$refs.remove.close();
			this.onInput();

			// if pagination page doesn't exist anymore,
			// go to previous page
			if (this.paginatedItems.length === 0 && this.page > 1) {
				this.page--;
			}

			this.items = this.sort(this.items);
		},
		/**
		 * When removal has been confirmed,
		 * remove all entries
		 */
		onRemoveAll() {
			this.items = [];
			this.onInput();
			this.$refs.dialogRemoveAll.close();
		},

		/**
		 * Edit the structure field entry at `index` position
		 * in the structure form with field `field` focused
		 * @public
		 * @param {number} index
		 * @param {string} field
		 */
		open(index, field) {
			this.currentIndex = index;
			this.currentModel = this.$helper.clone(this.items[index]);
			this.onFormOpen(field);
		},
		/**
		 * Update pagination state
		 * @param {Object} pagination
		 */
		paginate({ page }) {
			this.page = page;
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
		 * Saves the current entry with the values
		 * from the structure form and updates field value
		 */
		async save() {
			if (this.currentIndex !== null && this.currentIndex !== undefined) {
				try {
					await this.validate(this.currentModel);

					if (this.currentIndex === "new") {
						this.add(this.currentModel);
					} else {
						this.items[this.currentIndex] = this.currentModel;
					}

					this.items = this.sort(this.items);
					this.onInput();

					return true;
				} catch (errors) {
					this.$store.dispatch("notification/error", {
						message: this.$t("error.form.incomplete"),
						details: errors
					});

					throw errors;
				}
			}
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
		},
		/**
		 * Validayes the structure form
		 * @param {Object} model
		 * @returns {bool}
		 */
		async validate(model) {
			const errors = await this.$api.post(
				this.endpoints.field + "/validate",
				model
			);

			if (errors.length > 0) {
				throw errors;
			} else {
				return true;
			}
		},
		/**
		 * Triggered whenever any form field value changes
		 */
		onFormInput(model) {
			this.currentModel = model;
			this.$emit("formInput", model);
		}
	}
};
</script>

<style>
.k-structure-field:not([data-disabled="true"]) td.k-table-column {
	cursor: pointer;
}
</style>
