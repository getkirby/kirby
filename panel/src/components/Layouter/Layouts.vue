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

		<k-dialog
			ref="selector"
			:cancel-button="false"
			:submit-button="false"
			size="medium"
			class="k-layout-selector"
		>
			<k-headline>{{ $t("field.layout.select") }}</k-headline>
			<ul>
				<li
					v-for="(layoutOption, layoutOptionIndex) in layouts"
					:key="layoutOptionIndex"
					class="k-layout-selector-option"
				>
					<k-grid @click.native="addLayout(layoutOption)">
						<k-column
							v-for="(column, columnIndex) in layoutOption"
							:key="columnIndex"
							:width="column"
						/>
					</k-grid>
				</li>
			</ul>
		</k-dialog>

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
import Pasteboard from "@/components/Forms/Blocks/BlockPasteboard.vue";

/**
 * @internal
 */
export default {
	components: {
		"k-layout": Layout,
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

/** Selector **/
.k-layout-selector.k-dialog {
	background: #313740;
	color: var(--color-white);
}
.k-layout-selector .k-headline {
	line-height: 1;
	margin-top: -0.25rem;
	margin-bottom: 1.5rem;
}
.k-layout-selector ul {
	display: grid;
	grid-template-columns: repeat(3, 1fr);
	grid-gap: 1.5rem;
}
.k-layout-selector-option .k-grid {
	height: 5rem;
	grid-gap: 2px;
	box-shadow: var(--shadow);
	cursor: pointer;
}
.k-layout-selector-option:hover {
	outline: 2px solid var(--color-green-300);
	outline-offset: 2px;
}
.k-layout-selector-option:last-child {
	margin-bottom: 0;
}
.k-layout-selector-option .k-column {
	display: flex;
	background: rgba(255, 255, 255, 0.2);
	justify-content: center;
	font-size: var(--text-xs);
	align-items: center;
}
</style>
