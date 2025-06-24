<template>
	<div>
		<template v-if="hasFieldsets && rows.length">
			<k-draggable v-bind="draggableOptions" class="k-layouts" @sort="save">
				<k-layout
					v-for="(layout, index) in rows"
					:key="layout.id"
					v-bind="{
						...layout,
						disabled,
						endpoints,
						fieldsetGroups,
						fieldsets,
						isSelected: selected === layout.id,
						layouts,
						settings
					}"
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
		</template>

		<k-empty
			v-else-if="hasFieldsets === false"
			icon="dashboard"
			class="k-layout-empty"
		>
			{{ $t("field.blocks.fieldsets.empty") }}
		</k-empty>

		<k-empty v-else icon="dashboard" class="k-layout-empty" @click="select(0)">
			{{ empty ?? $t("field.layout.empty") }}
		</k-empty>
	</div>
</template>

<script>
import { props as LayoutProps } from "./Layout.vue";
import { id } from "@/mixins/props.js";

export const props = {
	mixins: [LayoutProps, id],
	props: {
		empty: String,
		min: Number,
		max: Number,
		selector: Object,
		value: {
			type: Array,
			default: () => []
		}
	}
};

/**
 * @unstable
 */
export default {
	mixins: [props],
	emits: ["input"],
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
				handle: true,
				list: this.rows
			};
		},
		hasFieldsets() {
			return this.$helper.object.length(this.fieldsets) > 0;
		}
	},
	watch: {
		value() {
			this.rows = this.value;
		}
	},
	methods: {
		copy(e, index) {
			// don't copy when there are not layouts
			if (this.rows.length === 0) {
				return false;
			}

			const copy = index !== undefined ? this.rows[index] : this.rows;
			this.$helper.clipboard.write(JSON.stringify(copy), e);

			// a sign that it has been pasted
			this.$panel.notification.success({
				message: this.$t("copy.success.multiple", { count: copy.length ?? 1 }),
				icon: "template"
			});
		},
		change(rowIndex, layout) {
			const columns = layout.columns.map((column) => column.width);
			const layoutIndex = this.layouts.findIndex(
				(layout) => layout.toString() === columns.toString()
			);

			this.$panel.dialog.open({
				component: "k-layout-selector",
				props: {
					label: this.$t("field.layout.change"),
					layouts: this.layouts,
					selector: this.selector,
					value: this.layouts[layoutIndex]
				},
				on: {
					submit: (value) => {
						this.onChange(value, layoutIndex, {
							rowIndex,
							layoutIndex,
							layout
						});

						this.$panel.dialog.close();
					}
				}
			});
		},
		duplicate(index, layout) {
			const copy = this.$helper.object.clone(layout);

			// replace all unique IDs for layouts, columns and blocks
			// the method processes a single object and returns it as an array
			const copies = this.updateIds(copy);

			this.rows.splice(index + 1, 0, ...copies);
			this.save();
		},
		async onAdd(columns) {
			let layout = await this.$api.post(this.endpoints.field + "/layout", {
				columns: columns
			});

			this.rows.splice(this.nextIndex, 0, layout);
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
			if (layoutIndex === this.layouts[payload.layoutIndex]) {
				return;
			}

			const oldLayout = payload.layout;

			// create empty layout based on selected columns
			const newLayout = await this.$api.post(this.endpoints.field + "/layout", {
				attrs: oldLayout.attrs,
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
						...this.$helper.object.clone(newLayout),
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
		},
		async paste(e, index = this.rows.length) {
			// pass json to the paste endpoint to validate
			let rows = await this.$api.post(this.endpoints.field + "/layout/paste", {
				json: this.$helper.clipboard.read(e)
			});

			if (rows.length) {
				this.rows.splice(index, 0, ...rows);
				this.save();
			}

			// a sign that it has been pasted
			this.$panel.notification.success({
				message: this.$t("paste.success", { count: rows.length }),
				icon: "download"
			});
		},
		pasteboard(index) {
			this.$panel.dialog.open({
				component: "k-block-pasteboard",
				on: {
					paste: (e) => this.paste(e, index)
				}
			});
		},
		remove(layout) {
			const index = this.rows.findIndex((element) => element.id === layout.id);

			if (index !== -1) {
				this.$delete(this.rows, index);
			}

			this.save();
		},
		removeAll() {
			this.$panel.dialog.open({
				component: "k-remove-dialog",
				props: {
					text: this.$t("field.layout.delete.confirm.all")
				},
				on: {
					submit: () => {
						this.rows = [];
						this.save();
						this.$panel.dialog.close();
					}
				}
			});
		},
		save() {
			this.$emit("input", this.rows);
		},
		select(index) {
			this.nextIndex = index;

			if (this.layouts.length === 1) {
				return this.onAdd(this.layouts[0]);
			}

			this.$panel.dialog.open({
				component: "k-layout-selector",
				props: {
					layouts: this.layouts,
					selector: this.selector,
					value: null
				},
				on: {
					submit: (value) => {
						this.onAdd(value);
						this.$panel.dialog.close();
					}
				}
			});
		},
		updateAttrs(layoutIndex, attrs) {
			this.rows[layoutIndex].attrs = attrs;
			this.save();
		},
		updateColumn(args) {
			this.rows[args.index].columns[args.columnIndex].blocks = args.blocks;
			this.save();
		},
		/**
		 * Replace all unique IDs for layouts, columns and blocks
		 *
		 * @param {array|object} copy
		 * @returns {array}
		 */
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
