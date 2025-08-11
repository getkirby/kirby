<template>
	<k-field
		v-bind="$props"
		:class="['k-structure-field', $attrs.class]"
		:style="$attrs.style"
		@click.stop
	>
		<template v-if="hasFields && !disabled" #options>
			<k-button-group
				v-if="isSelecting"
				:buttons="batchEditingButtons"
				size="xs"
				variant="filled"
			/>
			<k-button-group v-else>
				<template v-if="canSelect">
					<k-button v-bind="batchEditingToggle" size="xs" variant="filled" />
				</template>
				<k-button-group layout="collapsed">
					<k-button
						:autofocus="autofocus"
						:disabled="!more"
						:responsive="true"
						:text="$t('add')"
						icon="add"
						variant="filled"
						size="xs"
						@click="add()"
					/>
					<k-button
						icon="dots"
						size="xs"
						variant="filled"
						@click="$refs.options.toggle()"
					/>
					<k-dropdown-content
						ref="options"
						:options="[
							{
								click: () => add(),
								disabled: !more,
								icon: 'add',
								text: $t('add')
							},
							{
								click: () => removeAll(),
								disabled: items.length === 0 || disabled,
								icon: 'trash',
								text: $t('delete.all')
							}
						]"
						align-x="end"
					/>
				</k-button-group>
			</k-button-group>
		</template>

		<k-input-validator
			v-bind="{ min, max, required }"
			:value="JSON.stringify(items)"
		>
			<template v-if="hasFields">
				<!-- Empty State -->
				<k-empty v-if="items.length === 0" icon="list-bullet" @click="add()">
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
						:pagination="limit && !isSelecting ? pagination : false"
						:rows="paginatedItems"
						:selecting="isSelecting"
						:sortable="isSortable"
						@cell="open($event.row, $event.columnIndex)"
						@input="onTableInput"
						@option="option"
						@paginate="paginate"
						@select="onSelect"
					/>

					<footer v-if="more">
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
		</k-input-validator>
	</k-field>
</template>

<script>
import { props as Field } from "@/components/Forms/Field.vue";
import batchEditing from "@/mixins/batchEditing";

export default {
	mixins: [Field, batchEditing],
	inheritAttrs: false,
	props: {
		autofocus: Boolean,
		/**
		 * Whether to enable batch editing
		 */
		batch: {
			type: Boolean,
			default: false
		},
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
	emits: ["input"],
	data() {
		return {
			items: [],
			page: 1
		};
	},
	computed: {
		batchDeleteConfirmMessage() {
			return this.$t(`field.structure.delete.confirm.selected`, {
				count: this.selected.length
			});
		},

		batchEditingEvent() {
			return "structure.selecting";
		},

		batchEditingIdentifier() {
			return "_id";
		},

		canSelect() {
			return this.batch === true && this.items.length > 0;
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
		hasFields() {
			return this.$helper.object.length(this.fields) > 0;
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
		 * Returns array of options for dropdown in rows
		 * @returns {Array}
		 */
		options() {
			if (this.disabled) {
				return [];
			}

			return [
				{
					icon: "edit",
					text: this.$t("edit"),
					click: "edit"
				},
				{
					disabled: !this.duplicate || !this.more,
					icon: "copy",
					text: this.$t("duplicate"),
					click: "duplicate"
				},
				"-",
				{
					icon: "trash",
					text: this.$t("delete"),
					click: "remove"
				}
			];
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
		}
	},
	watch: {
		value: {
			handler(value) {
				this.stopSelecting();

				if (value !== this.items) {
					this.items = this.toItems(value);
				}
			},
			immediate: true
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

			value ??= this.$helper.field.form(this.fields);

			// add a unique id, if it's not already defined
			value._id ??= this.$helper.uuid();

			if (this.prepend === true) {
				this.items.unshift(value);
			} else {
				this.items.push(value);
			}

			this.save();

			// opening the drawer only works once the input event has been emitted
			this.open(value);
		},

		close() {
			this.$panel.drawer.close(this.id);
		},

		/**
		 * Focuses the add button
		 * @public
		 */
		focus() {
			this.$refs.add?.focus?.();
		},
		/**
		 * Config for the structure form
		 * @returns {Object}
		 */
		form(autofocus) {
			const fields = this.$helper.field.subfields(this, this.fields);

			// set the autofocus to the matching field in the form
			if (autofocus) {
				for (const field in fields) {
					fields[field].autofocus = field === autofocus;
				}
			}

			return fields;
		},

		findIndex(item) {
			return this.items.findIndex((row) => row._id === item._id);
		},

		navigate(item, step) {
			const index = this.findIndex(item);

			if (index === -1) {
				return;
			}

			this.open(this.items[index + step], null, true);
		},

		/**
		 * Edit the structure field entry at `index` position
		 * in the structure form with field `field` focused
		 * @public
		 * @param {object} item
		 * @param {string} field
		 */
		open(item, field, replace = false) {
			const index = this.findIndex(item);

			if (index === -1) {
				return false;
			}

			this.$panel.drawer.open({
				component: "k-structure-drawer",
				id: this.id,
				props: {
					disabled: this.disabled,
					icon: this.icon ?? "list-bullet",
					next: this.items[index + 1],
					prev: this.items[index - 1],
					tabs: {
						content: {
							fields: this.form(field)
						}
					},
					title: this.label,
					value: item
				},
				replace: replace,
				on: {
					input: (value) => {
						const index = this.findIndex(item);

						// update the prev/next navigation
						this.$panel.drawer.props.next = this.items[index + 1];
						this.$panel.drawer.props.prev = this.items[index - 1];

						this.items[index] = value;
						this.save();
					},
					next: () => {
						this.navigate(item, 1);
					},
					prev: () => {
						this.navigate(item, -1);
					},
					remove: () => {
						this.remove(item);
					}
				}
			});
		},

		/**
		 * Called when option from row's dropdown was engaged
		 * @param {string} option option name that was triggered
		 * @param {Object} row
		 */
		option(option, row) {
			switch (option) {
				case "remove":
					this.remove(row);
					break;

				case "duplicate":
					this.add({
						...this.$helper.object.clone(row),
						_id: this.$helper.uuid()
					});
					break;

				case "edit":
					this.open(row);
					break;
			}
		},

		onBatchDelete() {
			this.removeSelected();
		},

		/**
		 * Merges the updated values from the paginated table
		 * into the original items array and saves them
		 * @param {Array} values
		 */
		onTableInput(values) {
			if (this.limit) {
				values = this.items.toSpliced(
					this.pagination.offset,
					this.limit,
					...values
				);
			}

			this.save(values);
		},

		/**
		 * Update pagination state
		 * @param {Object} pagination
		 */
		paginate({ page }) {
			this.page = page;
			this.stopSelecting();
		},

		/**
		 * Remove current entry
		 */
		remove(item) {
			const index = this.findIndex(item);

			if (this.disabled || index === -1) {
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

		removeSelected() {
			this.items = this.items.filter((item) => !this.selected.includes(item));
			this.save();
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

			return this.$helper.array.sortBy(items, this.sortBy);
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

			value = value.map((row) => {
				return {
					_id: row._id ?? this.$helper.uuid(),
					...row
				};
			});

			return this.sort(value);
		}
	}
};
</script>

<style>
.k-structure-field td.k-table-column {
	cursor: pointer;
}
.k-structure-field .k-table + footer {
	display: flex;
	justify-content: center;
	margin-top: var(--spacing-3);
}

/* Allow interaction with disabled structure field to open the drawer */
.k-structure-field[data-disabled="true"] {
	cursor: initial;
}
.k-structure-field[data-disabled="true"] * {
	pointer-events: initial;
}
</style>
