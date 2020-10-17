<template>
  <div>
    <k-draggable
      v-bind="draggableOptions"
      :data-compact="compact"
      :data-empty="blocks.length === 0"
      class="k-blocks"
      @sort="onInput"
    >
      <k-block
        v-for="(block, index) in blocks"
        :ref="'block-' + block.id"
        :key="block.id"
        :compact="compact"
        :endpoints="endpoints"
        :fieldset="fieldsets[block.type]"
        :is-full="isFull"
        @append="select(index + 1)"
        @close="onClose(block)"
        @duplicate="duplicate(block, index)"
        @hide="hide(block)"
        @open="onOpen(block)"
        @prepend="select(index)"
        @remove="remove(block)"
        @show="show(block)"
        @update="updateContent(block, $event)"
        v-bind="block"
      />
      <template #footer>
        <k-empty
          icon="box"
          class="k-blocks-empty"
          @click="select(blocks.length)"
        >
          {{ empty || $t("field.builder.empty") }}
        </k-empty>
      </template>
    </k-draggable>

    <k-block-selector
      ref="selector"
      :endpoint="endpoints.field + '/fieldsets'"
      :fieldsets="fieldsets"
      @add="add"
    />

    <k-remove-dialog ref="removeAll" @submit="removeAll">
      {{ $t("field.builder.delete.all.confirm") }}
    </k-remove-dialog>

  </div>
</template>

<script>
import Block from "./Block.vue";
import BlockSelector from "./BlockSelector.vue";

export default {
  inheritAttrs: false,
  components: {
    "k-block": Block,
    "k-block-selector": BlockSelector,
  },
  props: {
    compact: Boolean,
    empty: String,
    endpoints: Object,
    fieldsets: Object,
    group: String,
    max: {
      type: Number,
      default: null,
    },
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
      nextIndex: this.value.length,
      opened: [],
    };
  },
  computed: {
    draggableOptions() {
      return {
        id: this._uid,
        handle: true,
        list: this.blocks,
        move: this.move,
        data: {
          fieldsets: this.fieldsets,
          isFull: this.isFull
        },
        options: {
          group: this.group
        }
      };
    },
    isEmpty() {
      return this.blocks.length === 0;
    },
    isFull() {
      if (this.max === null) {
        return false;
      }

      return this.blocks.length >= this.max;
    }
  },
  watch: {
    value() {
      this.blocks = this.value;
    }
  },
  methods: {
    async add(block) {
      this.blocks.splice(this.nextIndex, 0, block);
      this.onInput();
      this.$nextTick(() => {
        this.open(block);
      });
    },
    close(block) {
      this.$refs["block-" + block.id][0].close();
    },
    closeAll() {
      this.blocks.forEach(block => {
        this.close(block);
      });
    },
    confirmToRemoveAll() {
      this.$refs.removeAll.open();
    },
    async duplicate(block, index) {
      const response = await this.$api.get(this.endpoints.field + "/uuid");
      const copy = {
        ...this.$helper.clone(block),
        id: response["uuid"]
      };
      this.blocks.splice(index + 1, 0, block);
      this.onInput();
    },
    hide(block) {
      if (Array.isArray(block.attrs) === true) {
        this.$set(block, "attrs", {});
      }

      this.$set(block.attrs, "hide", true);
      this.onInput();
    },
    move(event) {
      // moving block between fields
      if (event.from !== event.to) {
        const block = event.draggedContext.element;
        const to    = event.relatedContext.component.componentData || event.relatedContext.component.$parent.componentData;

        // fieldset is not supported in target field
        if (Object.keys(to.fieldsets).includes(block.type) === false) {
          return false;
        }

        // target field has already reached max number of blocks
        if (to.isFull === true) {
          return false;
        }
      }

      return true;
    },
    onClose(block) {
      const index = this.opened.indexOf(block.id);
      this.$delete(this.opened, index);
      this.$emit("close", this.opened);
    },
    onInput() {
      this.$emit("input", this.blocks);
    },
    onOpen(block) {
      if (this.opened.includes(block.id) === false) {
        this.opened.push(block.id);
        this.$emit("open", this.opened);
      }
    },
    open(block, focus = true) {
      this.$refs["block-" + block.id][0].open(null, focus);
    },
    openAll() {
      this.blocks.forEach(block => {
        this.open(block, false);
      });
    },
    remove(block) {
      const index = this.blocks.findIndex(element => element.id === block.id);

      if (index !== -1) {
        this.$delete(this.blocks, index);
        this.onClose(block);
        this.onInput();
      }
    },
    removeAll() {
      this.blocks = [];
      this.nextIndex = null;
      this.onInput();
      this.$refs.removeAll.close();
    },
    select(index) {
      this.nextIndex = index;

      if (Object.keys(this.fieldsets).length === 1) {
        const type = Object.values(this.fieldsets)[0].type;
        this.add(type);
      } else {
        this.$refs.selector.open();
      }
    },
    show(block) {
      if (Array.isArray(block.attrs) === true) {
        this.$set(block, "attrs", {});
      }

      this.$set(block.attrs, "hide", false);
      this.onInput();
    },
    toggleAll() {
      if (this.opened.length === 0) {
        this.openAll();
      } else {
        this.closeAll();
      }
    },
    updateContent(block, content) {
      this.$set(block, "content", content);
      this.onInput();
    }
  }
};
</script>

<style lang="scss">
.k-blocks {
  background: $color-white;
  box-shadow: $shadow;
  border-radius: $rounded;
  padding: 1.5rem 0;
}
.k-blocks[data-compact] {
  padding: .75rem;
}
.k-blocks[data-empty] {
  padding: 0;
  background: none;
  box-shadow: none;
}
.k-blocks .k-sortable-ghost {
  outline: 2px solid $color-focus;
  cursor: grabbing;
  cursor: -moz-grabbing;
  cursor: -webkit-grabbing;
}
.k-blocks-empty.k-empty {
  cursor: pointer;
  display: flex;
  align-items: center;
}
.k-blocks > .k-blocks-empty:not(:only-child) {
  display: none;
}
</style>
