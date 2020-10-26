<template>
  <div>
    <k-block-layouts
      v-bind="$props"
      @edit="editLayout"
      @input="$emit('input', $event)"
      v-if="!isEditing"
    />

    <k-overlay
      ref="editor"
      :dimmed="false"
      class="k-layouter"
      @open="isEditing = true"
      @close="isEditing = false"
    >
      <div class="k-layouter-box" @mousedown.stop>
        <div class="k-layouter-panel k-layouter-preview">
          <header class="k-layouter-header">
            <k-headline class="k-layouter-headline">Layout</k-headline>
          </header>
          <div class="k-layouter-body">
            <k-block-layouts
              v-bind="$props"
              :current-block="currentBlock"
              :current-column="currentColumn"
              :current-layout="currentLayout"
              @edit="editLayout"
            />
          </div>
        </div>
        <div class="k-layouter-panel k-layouter-editor">
          <header class="k-layouter-header">
            <k-headline class="k-layouter-headline">Blocks</k-headline>
            <k-button class="k-layouter-submit-button" icon="check" @click="$refs.editor.close()">Done</k-button>
          </header>
          <div class="k-layouter-body">
            <k-blocks
              v-if="edit"
              ref="blocks"
              :compact="false"
              :endpoints="endpoints"
              :fieldsets="fieldsets"
              :max="max"
              :value="edit.column.blocks"
              class="k-layouter-blocks"
              @input="updateBlocks($event)"
            />
          </div>
        </div>
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
    layoutOptions: Array,
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
      return this.edit && this.edit.block ? this.edit.block.id : null;
    },
    currentColumn() {
      return this.edit ? this.edit.column.id : null;
    },
    currentLayout() {
      return this.edit ? this.edit.layout.id : null;
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
          this.$nextTick(() => {
            this.$refs.blocks.openAll();

            if (params.block) {
              this.$refs.blocks.open(params.block, params.tab, true);
            }
          });
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
.k-layouter {
  display: flex;
  justify-content: flex-end;
}
.k-layouter-box {
  position: relative;
  background: #313740;
  display: flex;
  width: 100%;
}
.k-layouter-panel {
  flex-basis: 50%;
  display: flex;
  flex-shrink: 0;
  flex-direction: column;
  min-width: 0;
}
.k-layouter-header {
  height: 2.5rem;
  line-height: 1;
  padding: 0 3rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-shrink: 0;
}
.k-layouter-headline {
  font-weight: $font-normal;
  font-size: $text-sm;
  line-height: 1;
}
.k-layouter-submit-button .k-icon {
  color: $color-green-400;
}
.k-layouter-body {
  padding-top: .75rem;
  padding-left: 3rem;
  padding-right: 3rem;
  padding-bottom: 6rem;
  overflow: auto;
  flex-grow: 1;
}

.k-layouter-preview {
  background: $color-background;
}

.k-layouter-editor .k-layouter-header {
  color: $color-white;
}
.k-layouter-editor .k-layouter-headline {
  font-weight: $font-normal;
}
.k-layouter-blocks > .k-builder-blocks > .k-builder-block:not([data-compact]) {
  background: $color-background;
  box-shadow: $shadow-md;
}
.k-layouter-blocks > .k-builder-blocks > .k-builder-blocks-empty {
  border-color: rgba(#fff, .1);
}

</style>
