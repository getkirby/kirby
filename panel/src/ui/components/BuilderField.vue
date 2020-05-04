<template>
  <k-field
    :input="_uid"
    v-bind="$props"
    class="k-builder-field"
  >
    <k-draggable
      :handle="true"
      :options="dragOptions"
      :list="blocks"
      element="k-grid"
      class="k-builder-field-grid"
      :style="`--columns: ${columns};`"
      @end="onSort"
    >
      <!-- Blocks -->
      <k-builder-block
        v-for="(block, blockIndex) in blocks"
        :key="blockIndex"
        ref="block"
        v-model="block.value"
        v-bind="fieldsets[block.type]"
        :more="more"
        @input="onInput"
        @duplicate="onDuplicate(block, blockIndex)"
        @insert="openCreateDialog(blockIndex, $event)"
        @remove="openRemoveDialog(blockIndex)"
        @preview="openPreview(block)"
      />

      <!-- Add zone -->
      <k-empty
        v-if="more"
        slot="footer"
        layout="list"
        class="cursor-pointer flex justify-center"
        @click="openCreateDialog(blocks.length)"
      >
        <k-button icon="add">
          Add block
        </k-button>
      </k-empty>
    </k-draggable>

    <!-- Preview drawer -->
    <k-builder-preview
      ref="preview"
      :field="label"
    />

    <!-- Create dialog -->
    <k-dialog
      ref="create"
      :submit-button="false"
      @close="closeCreateDialog"
    >
      <k-items
        :items="fieldsetItems"
        :sortable="false"
        @option="onInsert"
      />
    </k-dialog>

    <!-- Remove dialog -->
    <k-remove-dialog
      ref="remove"
      text="Do you really want to delete this block?"
      @close="closeRemoveDialog"
      @submit="onRemove"
    />
  </k-field>
</template>

<script>
import Field from "./Field.vue";

export default {
  inheritAttrs: false,
  props: {
    ...Field.props,
    /**
     * Blocks will be placed in a grid, if higher than 1
     */
    columns: {
      type: Number,
      default: 1
    },
    /**
     * Object of all available fieldset definitions
     */
    fieldsets: {
      type: Object,
      default() {
        return {};
      }
    },
    /**
     * Maximum number of blocks
     */
    max: Number,
    value: {
      type: Array,
      default() {
        return [];
      }
    }
  },
  data() {
    return {
      blocks: this.value,
      createBlockIndex: null,
      removeBlockIndex: null
    };
  },
  computed: {
    dragOptions() {
      return {
        draggable: ".k-builder-block"
      };
    },
    fieldsetItems() {
      return Object.keys(this.fieldsets).map(key => {
        return {
          title: this.fieldsets[key].name || this.fieldsets[key].label,
          icon: { type: this.fieldsets[key].icon || "dashboard" },
          fieldset: key,
          options: [
            { icon: "add", text: "Add" }
          ]
        };
      });
    },
    more() {
      if (this.disabled === true) {
        return false;
      }

      if (this.max && this.blocks.length >= this.max) {
        return false;
      }

      return true;
    },
  },
  watch: {
    value(value) {
      this.blocks = value;
    }
  },
  methods: {
    closeCreateDialog() {
      this.createBlockIndex = null;
    },
    closeRemoveDialog() {
      this.removeBlockIndex = null;
    },
    onDuplicate(block, index) {
      this.blocks = this.blocks.splice(index, 0, block);
      this.onInput();
    },
    onInput() {
      this.$emit("input", this.blocks);
    },
    onInsert(click, option) {
      const index = this.createBlockIndex;
      this.blocks.splice(index, 0, {
        type: option.fieldset,
        value: {}
      });
      this.$refs.create.close();
      this.onInput();
      this.$nextTick(() => {
        this.$refs["block"][index].open();
      });
    },
    onRemove(index) {
      this.blocks = this.blocks.splice(this.removeBlockIndex, 1);
      this.$refs.remove.close();
      this.onInput();
    },
    onSort() {
      this.$emit("input", this.blocks);
    },
    openCreateDialog(index, offset = 0) {
      this.createBlockIndex = index + offset;
      this.$refs.create.open();
    },
    openPreview(block) {
      this.$refs.preview.open({
        block: block,
        fieldset: this.fieldsets[block.type] || {}
      });
    },
    openRemoveDialog(index) {
      this.removeBlockIndex = index;
      this.$refs.remove.open();
    }
  }
}
</script>

<style lang="scss">
.k-builder-block-content > header {
  display: flex;
  justify-content: space-between;
  align-content: center;
}

/**
* Ugly fix because of .k-fieldset .k-grid rule
*/
.k-grid.k-builder-field-grid {
  --col-gap: .5rem;
  --row-gap: .5rem;
  align-items: start;
  grid-column-gap: var(--col-gap) !important;
  grid-row-gap: var(--row-gap) !important;
}
</style>
