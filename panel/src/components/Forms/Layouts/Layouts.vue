<template>
	<div>
		<template v-if="rows.length">
			<k-draggable v-bind="draggableOptions" class="k-layouts" @sort="save">
				<k-layout
					v-for="(layout, index) in rows"
					v-bind="layout"
					:key="layout.id"
					:disabled="disabled"
					:endpoints="endpoints"
					:fieldset-groups="fieldsetGroups"
					:fieldsets="fieldsets"
					:is-selected="selected === layout.id"
					:settings="settings"
					@append="select(index + 1)"
					@change="change(index, layout)"
					@copy="copy($event, index)"
					@duplicate="duplicate(index, layout)"
					@paste="pasteboard(index + 1)"
					@prepend="select(index)"
					@remove="remove(layout)"
					@select="selected = layout.id"
					@updateAttrs="updateAttrs(index, $event)"
					@updateColumn="updateColumn({ layout, index, ...$event })"
				/>
			</k-draggable>

			<k-button
				v-if="!disabled"
				class="k-field-add-item-button"
				icon="add"
				:tooltip="$t('add')"
				@click="select(rows.length)"
			/>
		</template>
		<template v-else>
			<k-empty icon="dashboard" class="k-layout-empty" @click="select(0)">
				{{ empty || $t("field.layout.empty") }}
			</k-empty>
		</template>

		<k-layout-selector ref="selector" :layouts="layouts" @select="onSelect" />
		<k-remove-dialog
			ref="removeAll"
			:text="$t('field.layout.delete.confirm.all')"
			@submit="onRemoveAll"
		/>
		<k-block-pasteboard ref="pasteboard" @paste="onPaste" />
	</div>
</template>

<script>
/**
 * @internal
 */
export default {
	props: {
		disabled: Boolean,
		empty: String,
		endpoints: Object,
		fieldsetGroups: Object,
		fieldsets: Object,
		layouts: Array,
		max: Number,
		settings: Object,
		value: Array
	},
	data() {
		return {
			current: null,
			nextIndex: null,
			rows: this.value,
			selected: null
		};
	},
	computed: {
		draggableOptions() {
			return {
				id: this._uid,
				handle: true,
				list: this.rows
			};
		}
	},
	watch: {
		value() {
			this.rows = this.value;
		}
	},
	methods: {
		confirmRemoveAll() {
			this.$refs.removeAll.open();
		},
		copy(e, index) {
			// don't copy when there are not layouts
			if (this.rows.length === 0) {
				return false;
			}

			const copy = index !== undefined ? this.rows[index] : this.rows;
			this.$helper.clipboard.write(JSON.stringify(copy), e);

			// a sign that it has been pasted
			this.$store.dispatch(
				"notification/success",
				this.$t("copy.success", { count: copy.length ?? 1 })
			);
		},
		change(rowIndex, layout) {
			const columns = layout.columns.map((column) => column.width);
			const layoutIndex = this.layouts.findIndex(
				(layout) => layout.toString() === columns.toString()
			);

			// data required to change the layout both in the dialog and afterwards
			this.$refs.selector.open({ rowIndex, layoutIndex, layout });
		},
		duplicate(index, layout) {
			let copy = {
				...this.$helper.clone(layout),
				id: this.$helper.uuid()
			};

			// replace all unique IDs for columns and blocks
			copy = this.updateIds(copy);

			this.rows.splice(index + 1, 0, copy);
			this.save();
		},
		async onAdd(columns) {
			let layout = await this.$api.post(this.endpoints.field + "/layout", {
				columns: columns
			});

			this.rows.splice(this.nextIndex, 0, layout);

			if (this.layouts.length > 1) {
				this.$refs.selector.close();
			}

			this.save();
		},
		/**
		 * Working logic of changing layout:
		 * - If the new layout has more columns,
		 *   as many as needed are filled in order from the start.
		 * - If the new layout has fewer columns,
		 *   all are filled in order from the start and as many
		 *   additional layout rows are added as needed.
		 *
		 * @param {array} columns
		 * @param {number} layoutIndex
		 * @param {object|null} payload
		 * @returns {Promise<void>}
		 */
		async onChange(columns, layoutIndex, payload) {
			// don't do anything if the same layout got selected
			if (layoutIndex === payload.layoutIndex) {
				return this.$refs.selector.close();
			}

			const oldLayout = payload.layout;

			// create empty layout based on selected columns
			const newLayout = await this.$api.post(this.endpoints.field + "/layout", {
				columns: columns
			});

			// filter columns that have blocks
			const oldColumns = oldLayout.columns.filter(
				(column) => column?.blocks?.length > 0
			);

			// start collecting new rows
			const rows = [];

			// if the layout row was completely empty,
			// just switch it to the new layout
			if (oldColumns.length === 0) {
				rows.push(newLayout);
			} else {
				// otherwise check how many chunks (columns per layout rows)
				// we need to host all filled columns
				const chunks =
					Math.ceil(oldColumns.length / newLayout.columns.length) *
					newLayout.columns.length;

				// move throught the new layout rows in steps of columns per row
				for (let i = 0; i < chunks; i += newLayout.columns.length) {
					const copy = {
						...this.$helper.clone(newLayout),
						id: this.$helper.uuid()
					};

					// move blocks to new layout from old
					copy.columns = copy.columns.map((column, columnIndex) => {
						column.blocks = oldColumns[columnIndex + i]?.blocks ?? [];
						return column;
					});

					// add row only if any of its columns has any blocks
					if (copy.columns.filter((column) => column?.blocks?.length).length) {
						rows.push(copy);
					}
				}
			}

			// remove old layout row and add new rows in one go
			this.rows.splice(payload.rowIndex, 1, ...rows);

			this.save();
			this.$refs.selector.close();
		},
		async onPaste(e) {
			const json = this.$helper.clipboard.read(e);
			const index = this.current ?? this.rows.length;

			// pass json to the paste endpoint to validate
			let rows = await this.$api.post(this.endpoints.field + "/layout/paste", {
				json: json
			});

			if (rows.length) {
				this.rows.splice(index, 0, ...rows);
				this.save();
			}

			// a sign that it has been pasted
			this.$store.dispatch(
				"notification/success",
				this.$t("paste.success", { count: rows.length })
			);
		},
		onRemoveAll() {
			this.rows = [];
			this.save();
			this.$refs.removeAll.close();
		},
		async onSelect(columns, layoutIndex, payload) {
			return payload
				? this.onChange(columns, layoutIndex, payload)
				: this.onAdd(columns);
		},
		pasteboard(index) {
			this.current = index;
			this.$refs.pasteboard.open();
		},
		remove(layout) {
			const index = this.rows.findIndex((element) => element.id === layout.id);

			if (index !== -1) {
				this.$delete(this.rows, index);
			}

			this.save();
		},
		save() {
			this.$emit("input", this.rows);
		},
		select(index) {
			this.nextIndex = index;

			if (this.layouts.length === 1) {
				return this.onAdd(this.layouts[0]);
			}

			this.$refs.selector.open();
		},
		updateAttrs(layoutIndex, attrs) {
			this.rows[layoutIndex].attrs = attrs;
			this.save();
		},
		updateColumn(args) {
			this.rows[args.index].columns[args.columnIndex].blocks = args.blocks;
			this.save();
		},
		updateIds(copy) {
			if (Array.isArray(copy) === false) {
				copy = [copy];
			}

			return copy.map((layout) => {
				layout.id = this.$helper.uuid();
				layout.columns = layout.columns.map((column) => {
					column.id = this.$helper.uuid();

					column.blocks = column.blocks.map((block) => {
						block.id = this.$helper.uuid();
						return block;
					});

					return column;
				});

				return layout;
			});
		}
	}
};
</script>

<style>
.k-layouts .k-sortable-ghost {
	position: relative;
	box-shadow: rgba(17, 17, 17, 0.25) 0 5px 10px;
	outline: 2px solid var(--color-focus);
	cursor: grabbing;
	z-index: 1;
}
</style>
