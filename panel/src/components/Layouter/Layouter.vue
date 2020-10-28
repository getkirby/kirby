<template>
  <div class="k-layouter">
    <k-block-layouts
      v-bind="$props"
      @edit="editLayout"
      @input="$emit('input', $event)"
    />
    <k-overlay
      ref="editor"
      :dimmed="false"
      class="k-layouter-overlay"
      @open="isEditing = true"
      @close="isEditing = false"
    >
      <div class="k-layouter-box">
        <k-blocks
          v-if="currentColumn"
          ref="blocks"
          :endpoints="endpoints"
          :fieldsets="fieldsets"
          :max="max"
          :value="currentColumn.blocks"
          group="layout"
          @input="updateBlocks"
        />
      </div>
    </k-overlay>

  </div>
</template>

<script>
import Layouts from "./Layouts.vue";

export default {
  components: {
    "k-block-layouts": Layouts,
  },
  props: {
    endpoints: Object,
    fieldsets: Object,
    layouts: Array,
    max: Number,
    value: Array
  },
  data() {
    return {
      edit: null,
      isEditing: false,
    };
  },
  computed: {
    currentBlock() {
      return this.edit && this.edit.block ? this.edit.block : null;
    },
    currentColumn() {
      return this.edit ? this.edit.column : null;
    },
    currentLayout() {
      return this.edit ? this.edit.layout : null;
    }
  },
  methods: {
    editLayout(params) {
      this.edit = params;

      if (this.isEditing === false) {
        this.$refs.editor.open();
      }

      this.$nextTick(() => {
        this.$nextTick(() => {
          if (params.block) {
            this.$refs.blocks.focusOrOpen(params.block);
          }
        });
      });
    },
    updateBlocks(blocks) {
      let layouts = this.$helper.clone(this.value);
      layouts[this.edit.layoutIndex].columns[this.edit.columnIndex].blocks = blocks;
      this.$emit("input", layouts);
    }
  }
};
</script>

<style lang="scss">
.k-layouter-overlay {
  display: flex;
  align-items: stretch;
  justify-content: flex-end;
  background: rgba(#000, .1);
}
.k-layouter-box {
  position: relative;
  width: 40rem;
  background: #fff;
  box-shadow: $shadow-xl;
  overflow-x: auto;
}
.k-layouter-box .k-blocks {
  box-shadow: none;
  background: none;
}
.k-layouter-box .k-blocks[data-empty] {
  padding: 3rem 4rem;
}

</style>
