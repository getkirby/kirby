<template>
  <div>
    <k-draggable
      v-bind="draggableOptions"
      :data-compact="compact"
      :data-empty="blocks.length === 0"
      class="k-blocks"
      @sort="save"
    >
      <k-block
        v-for="(block, index) in blocks"
        :ref="'block-' + block.id"
        :key="block.id"
        :endpoints="endpoints"
        :fieldset="fieldsets[block.type]"
        :is-full="isFull"
        :is-hidden="block.isHidden === true"
        :is-open="isOpen(block)"
        v-bind="block"
        @append="add($event, index + 1)"
        @choose="choose($event)"
        @chooseToAppend="choose(index + 1)"
        @chooseToPrepend="choose(index)"
        @close="close(block)"
        @convert="convert(block, $event)"
        @duplicate="duplicate(block, index)"
        @hide="hide(block)"
        @open="open(block)"
        @prepend="add($event, index)"
        @remove="remove(block)"
        @show="show(block)"
        @update="update(block, $event)"
      />
      <template #footer>
        <k-empty
          icon="box"
          class="k-blocks-empty"
          @click="choose(blocks.length)"
        >
          {{ empty || $t("field.builder.empty") }}
        </k-empty>
      </template>
    </k-draggable>

    <k-block-selector
      ref="selector"
      :fieldsets="fieldsets"
      @add="add"
    />

    <k-remove-dialog ref="removeAll" @submit="removeAll">
      {{ $t("field.builder.delete.all.confirm") }}
    </k-remove-dialog>

  </div>
</template>

<script>
import debounce from "@/helpers/debounce.js";

export default {
  inheritAttrs: false,
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
  created() {
    this.save = debounce(this.save, 250);
  },
  data() {
    return {
      blocks: this.value,
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
    async add(type = "paragraph", index) {
      const block = await this.$api.get(this.endpoints.field + "/fieldsets/" + type);
      this.blocks.splice(index, 0, block);
      this.save();
      this.$nextTick(() => {
        this.open(block);
      });
    },
    choose(index) {
      if (Object.keys(this.fieldsets).length === 1) {
        const type = Object.values(this.fieldsets)[0].type;
        this.add(type, index);
      } else {
        this.$refs.selector.open(index);
      }
    },
    close(block) {
      const index = this.opened.indexOf(block.id);
      this.$delete(this.opened, index);
      this.$emit("close", this.opened);
    },
    confirmToRemoveAll() {
      this.$refs.removeAll.open();
    },
    convert(block, type) {
      if (type === "blockquote") {
        type = "quote";
      }

      this.$set(block, "type", type);
    },
    async duplicate(block, index) {
      const response = await this.$api.get(this.endpoints.field + "/uuid");
      const copy = {
        ...this.$helper.clone(block),
        id: response["uuid"]
      };
      this.blocks.splice(index + 1, 0, block);
      this.save();
    },
    fieldset(block) {
      return this.fieldsets[block.type];
    },
    focus(block) {
      if (this.$refs["block-" + block.id]) {
        this.$refs["block-" + block.id][0].focus();
      }
    },
    hide(block) {
      this.$set(block, "isHidden", true);
      this.save();
    },
    isOpen(block) {
      return this.opened.includes(block.id);
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
    open(block, focus = true) {
      if (this.opened.includes(block.id) === false) {
        this.opened.push(block.id);
        this.$emit("open", this.opened);

        this.$nextTick(() => {
          this.focus(block);
        });
      }
    },
    remove(block) {
      const index = this.blocks.findIndex(element => element.id === block.id);

      if (index !== -1) {
        this.$delete(this.blocks, index);
        this.close(block);
        this.save();
      }
    },
    removeAll() {
      this.blocks = [];
      this.save();
      this.$refs.removeAll.close();
    },
    save() {
      this.$emit("input", this.blocks);
    },
    show(block) {
      this.$set(block, "isHidden", false);
      this.save();
    },
    update(block, content) {
      console.log(content);

      this.$set(block, "content", content);
      this.save();
    }
  }
};
</script>

<style lang="scss">
.k-blocks {
  background: $color-white;
  box-shadow: $shadow;
  border-radius: $rounded;
  padding: 3rem 0;
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
  background: rgba($color-blue-200, .5);
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
