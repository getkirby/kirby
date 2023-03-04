<template>
	<div>
		<template v-if="rows.length">
			<k-draggable v-bind="draggableOptions" class="k-layouts" @sort="save">
				<k-layout
					v-for="(layout, layoutIndex) in rows"
					v-bind="layout"
					:key="layout.id"
					:disabled="disabled"
					:endpoints="endpoints"
					:fieldset-groups="fieldsetGroups"
					:fieldsets="fieldsets"
					:is-selected="selected === layout.id"
					:settings="settings"
					@append="selectLayout(layoutIndex + 1)"
					@change="onChangeLayout(layoutIndex, layout)"
					@copy="copy($event, layoutIndex)"
					@duplicate="duplicateLayout(layoutIndex, layout)"
					@paste="pasteboard(layoutIndex + 1)"
					@prepend="selectLayout(layoutIndex)"
					@remove="removeLayout(layout)"
					@select="selected = layout.id"
					@updateAttrs="updateAttrs(layoutIndex, $event)"
					@updateColumn="
						updateColumn({
							layout,
							layoutIndex,
							...$event
						})
					"
				/>
			</k-draggable>

			<k-button
				v-if="!disabled"
				class="k-field-add-item-button"
				icon="add"
				:tooltip="$t('add')"
				@click="selectLayout(rows.length)"
			/>
		</template>
		<template v-else>
			<k-empty icon="dashboard" class="k-layout-empty" @click="selectLayout(0)">
				{{ empty || $t("field.layout.empty") }}
			</k-empty>
		</template>

		<k-layout-selector ref="selector" :layouts="layouts" @select="addLayout" />
		<k-layout-selector
			ref="changeSelector"
			:layouts="layouts"
			@select="changeLayout"
		/>

		<k-remove-dialog
			ref="removeAll"
			:text="$t('field.layout.delete.confirm.all')"
			@submit="removeAll"
		/>

		<k-block-pasteboard ref="pasteboard" @paste="paste" />
	</div>
</template>

<script>
import Layout from "./Layout.vue";
import LayoutSelector from "./LayoutSelector.vue";
import Pasteboard from "@/components/Forms/Blocks/BlockPasteboard.vue";

/**
 * @internal
 */
export default {
	components: {
		"k-layout": Layout,
		"k-layout-selector": LayoutSelector,
		"k-block-pasteboard": Pasteboard
	},
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
			currentLayout: null,
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
		async addLayout(columns) {
			let layout = await this.$api.post(this.endpoints.field + "/layout", {
				columns: columns
			});

			this.rows.splice(this.nextIndex, 0, layout);

			if (this.layouts.length > 1) {
				this.$refs.selector.close();
			}

			this.save();
		},
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
		/**
		 * Working logic of changing layout:
		 * - If the new layout has more columns, they are filled in order from the start.
		 * - If the new layout has fewer columns, all are filled in order from the start
		 * 	 and as many additional layout rows are added as needed.
		 *
		 * @param {array} columns
		 * @param {number} layoutIndex
		 * @param {object|null} payload
		 * @returns {Promise<void>}
		 */
		async changeLayout(columns, layoutIndex, payload) {
			// remove the layout at first
			this.rows.splice(payload.rowIndex, 1);

			// create empty layout based on selected
			const newLayout = await this.$api.post(this.endpoints.field + "/layout", {
				columns: columns
			});

			const oldLayout = payload.layout;

			// filter columns that have blocks
			const oldLayoutColumns = oldLayout.columns.filter(
				(column) => column?.blocks?.length > 0
			);

			// if the new layout has more columns
			// it determines how many times it should loop
			// to transfer all of them to the new layout
			const layoutChunks = Math.ceil(
				oldLayoutColumns.length / newLayout.columns.length
			);

			let copy, offset;
			for (let i = 0; i < layoutChunks; i++) {
				offset = i * newLayout.columns.length;

				copy = {
					...this.$helper.clone(newLayout),
					id: this.$helper.uuid()
				};

				// move blocks to new layout from old
				copy.columns = copy.columns.map((column, columnIndex) => {
					column.blocks = oldLayoutColumns[columnIndex + offset]?.blocks ?? [];
					return column;
				});

				// add layout row only if any column has blocks
				if (
					copy.columns.filter((column) => column?.blocks?.length > 0).length > 0
				) {
					this.rows.splice(payload.rowIndex + i, 0, copy);
				}
			}

			this.save();
			this.$refs.changeSelector.close();
		},
		duplicateLayout(index, layout) {
			let copy = {
				...this.$helper.clone(layout),
				id: this.$helper.uuid()
			};

			// replace all unique IDs for columns and blocks
			copy = this.updateIds(copy);

			this.rows.splice(index + 1, 0, copy);
			this.save();
		},
		filterUnallowedLayoutsFieldsets(layouts) {
			if (Array.isArray(layouts) === false) {
				layouts = [layouts];
			}

			// first filter unallowed layouts
			layouts = layouts.filter((layout) => {
				const columns = layout.columns.map((column) => column.width);
				const index = this.layouts.findIndex(
					(x) => JSON.stringify(x) === JSON.stringify(columns)
				);
				return index !== -1;
			});

			// then filter unallowed block/fieldsets
			layouts = layouts.map((layout) => {
				layout.columns = layout.columns.map((column) => {
					column.blocks = column.blocks.filter((block) =>
						Object.keys(this.fieldsets).includes(block.type)
					);
					return column;
				});
				return layout;
			});
			return layouts;
		},
		/**
		 * Finds which layout index it uses from the layout object
		 *
		 * @param {object} layout
		 * @returns {number}
		 */
		getLayoutIndex(layout) {
			const columns = layout.columns.map((column) => {
				return column.width;
			});

			return this.layouts.findIndex(
				(layout) => layout.toString() === columns.toString()
			);
		},
		onChangeLayout(rowIndex, layout) {
			// data required to change the layout both in the dialog and afterwards
			const payload = {
				rowIndex: rowIndex,
				layoutIndex: this.getLayoutIndex(layout),
				layout: layout
			};

			this.$refs.changeSelector.open(payload);
		},
		paste(e) {
			const copy = JSON.parse(this.$helper.clipboard.read(e));
			const index = this.currentLayout ?? this.rows.length;

			// throw out anything that isn't allowed.
			let rows = this.filterUnallowedLayoutsFieldsets(copy);

			// replace all unique IDs for columns and blocks
			rows = this.updateIds(rows);

			this.rows.splice(index, 0, ...rows);
			this.save();

			// a sign that it has been pasted
			this.$store.dispatch(
				"notification/success",
				this.$t("paste.success", { count: rows.length })
			);
		},
		pasteboard(index) {
			this.currentLayout = index;
			this.$refs.pasteboard.open();
		},
		removeLayout(layout) {
			const index = this.rows.findIndex((element) => element.id === layout.id);

			if (index !== -1) {
				this.$delete(this.rows, index);
			}

			this.save();
		},
		removeAll() {
			this.rows = [];
			this.save();
			this.$refs.removeAll.close();
		},
		save() {
			this.$emit("input", this.rows);
		},
		selectLayout(index) {
			this.nextIndex = index;

			if (this.layouts.length === 1) {
				this.addLayout(this.layouts[0]);
				return;
			}

			this.$refs.selector.open();
		},
		updateAttrs(layoutIndex, attrs) {
			this.rows[layoutIndex].attrs = attrs;
			this.save();
		},
		updateColumn(args) {
			this.rows[args.layoutIndex].columns[args.columnIndex].blocks =
				args.blocks;
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
	cursor: -moz-grabbing;
	cursor: -webkit-grabbing;
	z-index: 1;
}
</style>
