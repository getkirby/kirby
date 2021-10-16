<template>
  <div>
    <template v-if="rows.length">
      <k-draggable v-bind="draggableOptions" class="k-layouts" @sort="save">
        <k-layout
          v-for="(layout, layoutIndex) in rows"
          :key="layout.id"
          :disabled="disabled"
          :endpoints="endpoints"
          :fieldset-groups="fieldsetGroups"
          :fieldsets="fieldsets"
          :is-selected="selected === layout.id"
          :settings="settings"
          v-bind="layout"
          @append="selectLayout(layoutIndex + 1)"
          @duplicate="duplicateLayout(layoutIndex, layout)"
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
        class="k-layout-add-button"
        icon="add"
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
  </div>
</template>

<script>
import Layout from "./Layout.vue";

/**
 * @internal
 */
export default {
  components: {
    "k-layout": Layout
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
    duplicateLayout(index, layout) {
      let copy = {
        ...this.$helper.clone(layout),
        id: this.$helper.uuid()
      };

      // replace all unique IDs for columns and blocks
      copy.columns = copy.columns.map((column) => {
        column.id = this.$helper.uuid();
        column.blocks = column.blocks.map((block) => {
          block.id = this.$helper.uuid();
          return block;
        });

        return column;
      });

      this.rows.splice(index + 1, 0, copy);
      this.save();
    },
    removeLayout(layout) {
      const index = this.rows.findIndex((element) => element.id === layout.id);

      if (index !== -1) {
        this.$delete(this.rows, index);
      }

      this.save();
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
    updateColumn(args) {
      this.rows[args.layoutIndex].columns[args.columnIndex].blocks =
        args.blocks;
      this.save();
    },
    updateAttrs(layoutIndex, attrs) {
      this.rows[layoutIndex].attrs = attrs;
      this.save();
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

/** Add Button **/
.k-layout-add-button {
  display: flex;
  align-items: center;
  width: 100%;
  color: var(--color-gray-500);
  justify-content: center;
  padding: 0.75rem 0;
}
.k-layout-add-button:hover {
  color: var(--color-black);
}
</style>
