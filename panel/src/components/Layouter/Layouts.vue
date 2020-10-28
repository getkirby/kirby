<template>
  <div>
    <template v-if="rows.length">
      <k-draggable :handle="true" class="k-layouts">
        <section
          v-for="(layout, layoutIndex) in rows"
          :key="layout.id"
          :data-current="layout.id === currentLayout"
          class="k-layout"
        >
          <k-sort-handle class="k-layout-handle" />
          <k-grid class="k-layout-columns">
            <div
              v-for="(column, columnIndex) in layout.columns"
              :key="columnIndex"
              :data-current="column.id == currentColumn"
              :data-width="column.width"
              :id="column.id"
              class="k-column k-layout-column"
            >
              <k-blocks
                :compact="true"
                :endpoints="endpoints"
                :fieldsets="fieldsets"
                :max="max"
                :value="column.blocks"
                group="layout"
                @click="$emit('edit', {
                  layout,
                  layoutIndex,
                  column,
                  columnIndex,
                  block: $event
                })"
                @input="updateBlocks({
                  layout,
                  layoutIndex,
                  column,
                  columnIndex,
                  blocks: $event
                })"
              />
            </div>
          </k-grid>
          <nav class="k-layout-options">
            <k-dropdown>
              <k-button icon="angle-down" @click="$refs['layout-' + layout.id][0].toggle()" />
              <k-dropdown-content :ref="'layout-' + layout.id" align="right">
                <k-dropdown-item icon="angle-up" @click="selectLayout(layoutIndex)">Insert before</k-dropdown-item>
                <k-dropdown-item icon="angle-down" @click="selectLayout(layoutIndex + 1)">Insert after</k-dropdown-item>
                <hr>
                <k-dropdown-item icon="trash" @click="removeLayout(layout)">Delete layout</k-dropdown-item>
              </k-dropdown-content>
            </k-dropdown>
          </nav>
        </section>
      </k-draggable>

      <k-button
        class="k-layout-add-button"
        icon="add"
        @click="selectLayout(rows.length)"
      />

    </template>
    <template v-else>
      <k-empty
        icon="dashboard"
        class="k-layout-empty"
        @click="selectLayout(0)"
      >
        No rows yet
      </k-empty>
    </template>

    <k-dialog
      ref="selector"
      :cancel-button="false"
      :submit-button="false"
      size="medium"
      class="k-layout-selector"
    >
      <k-headline>Select a layout</k-headline>
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
  </div>
</template>

<script>
export default {
  props: {
    currentBlock: [String, Number],
    currentColumn: [String, Number],
    currentLayout: [String, Number],
    endpoints: Object,
    fieldsets: Object,
    layouts: Array,
    max: Number,
    value: Array
  },
  data() {
    return {
      rows: this.value,
      nextIndex: null,
    };
  },
  watch: {
    value() {
      this.rows = this.value;
    }
  },
  methods: {
    addLayout(columns) {

      let layout = {
        id: this.$helper.uuid(),
        columns: []
      };

      columns.forEach(width => {
        layout.columns.push({
          id: this.$helper.uuid(),
          width: width,
          blocks: []
        });
      });

      this.rows.splice(this.nextIndex, 0, layout);
      this.$refs.selector.close();

      this.$emit("input", this.rows);
    },
    prependLayout() {
      this.$refs.selector.open();
    },
    removeLayout(layout) {
      const index = this.rows.findIndex(element => element.id === layout.id);

      if (index !== -1) {
        this.$delete(this.rows, index);
      }

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
    updateBlocks(args) {
      this.rows[args.layoutIndex].columns[args.columnIndex].blocks = args.blocks;
      this.$emit("input", this.rows);
    }
  }
};
</script>

<style lang="scss">
$layout-color-border: $color-blue-300;
$layout-padding: 0;

.k-layouts {
  background: $color-background;
  border-radius: $rounded;
  box-shadow: $shadow;
}
.k-layout {
  position: relative;
  margin: 0;
  border-radius: $rounded-sm;
  background: $color-background;
}
.k-layout:not(:last-child) {
  margin-bottom: 1px;
}
.k-layout .k-layout-handle,
.k-layout .k-layout-options {
  position: absolute;
  top: -1px;
  bottom: -1px;
  height: calc(100% + 2px);
  width: 1.5rem;
  left: -1.5rem;
  color: $color-gray-500;
}
.k-layout .k-layout-options {
  left: auto;
  right: -1.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
}
.k-layout .k-layout-options > .k-button {
  height: 100%;
  width: 100%;
}
.k-layout:hover .k-layout-options,
.k-layout:hover .k-layout-handle {
  color: $color-black;
}
.k-layout-columns.k-grid {
  grid-gap: 1px;
}
.k-layout-column {
  position: relative;
  padding: $layout-padding;
  height: 100%;
  background: #fff;
  cursor: pointer;
}
.k-layout-column > div {
  height: 100%;
}

.k-layout-column .k-block {
  box-shadow: none;
}

.k-layout-selector.k-dialog {
  background: #313740;
  color: $color-white;
}

.k-layout-selector .k-headline {
  margin-bottom: 1.5rem;
  line-height: 1;
  margin-top: -.25rem;
}

.k-layout-selector ul {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  grid-gap: 1.5rem;
}
.k-layout-selector-option .k-grid {
  height: 5rem;
  grid-gap: 2px;
  box-shadow: $shadow;
  cursor: pointer;
}
.k-layout-selector-option:hover {
  outline: 2px solid $color-green-300;
  outline-offset: 2px;
}
.k-layout-selector-option:last-child {
  margin-bottom: 0;
}
.k-layout-selector-option .k-column {
  display: flex;
  background: rgba(#fff, .2);
  justify-content: center;
  font-size: $text-xs;
  align-items: center;
}

.k-layout-column[data-current] {
  position: relative;
  z-index: 1;
  outline: 2px solid $color-blue-400;
}
.k-layout-column .k-blocks {
  background: none;
  box-shadow: none;
  padding: 0;
  height: 100%;
}
.k-layout-column .k-block-options {
  width: 2rem !important;
  left: 0rem !important;
  display: none;
}
.k-layout-column .k-block-options .k-sort-handle {
  display: none;
}
.k-layout-column .k-block-container {
  padding-left: 1.5rem;
  padding-right: 1.5rem;
}
.k-layout-column .k-blocks-empty.k-empty {
  border: 0;
  opacity: 1;
  min-height: 0;
}
.k-layout-column .k-blocks:hover {
  background: rgba($color-blue-200, .125);
}
.k-layout-column .k-blocks-empty.k-empty {
  padding: 3rem 1.5rem;
}
.k-layout-column .k-blocks-empty.k-empty > * {
  display: none;
}
.k-layout-column .k-blocks-empty.k-empty::after {
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  content: "";
}

.k-layout-add-button {
  display: flex;
  align-items: center;
  width: 100%;
  color: $color-gray-500;
  justify-content: center;
  padding: .75rem 0;
}
.k-layout-add-button:hover {
  color: $color-black;
}
</style>
