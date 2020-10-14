<template>
  <div>
    <k-draggable
      v-bind="draggableOptions"
      class="k-builder-blocks"
      @sort="onInput"
    >
      <k-builder-block
        v-for="(block, index) in blocks"
        :ref="'block-' + block.id"
        :key="block.id"
        :compact="compact"
        :endpoints="endpoints"
        :fieldset="fieldsets[block.type]"
        :is-full="isFull"
        @append="select(index + 1)"
        @close="onClose(block)"
        @duplicate="duplicate(block)"
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
          class="k-builder-field-empty"
          @click="select(blocks.length)"
        >
          {{ empty || $t("field.builder.empty") }}
        </k-empty>
      </template>
    </k-draggable>

    <k-dialog
      ref="fieldsets"
      :cancel-button="false"
      :submit-button="false"
      class="k-builder-fieldsets-dialog"
      size="large"
    >
      <k-headline>{{ $t("field.builder.fieldsets.label") }}</k-headline>
      <div class="k-builder-fieldsets">
        <k-button
          v-for="fieldset in fieldsets"
          :key="fieldset.name"
          :icon="fieldset.icon || 'box'"
          @click="add(fieldset.type)"
        >
          {{ $helper.string.template(fieldset.label) }}
        </k-button>
      </div>
    </k-dialog>

    <k-remove-dialog ref="removeAll" @submit="removeAll">
      {{ $t("field.builder.delete.all.confirm") }}
    </k-remove-dialog>

  </div>
</template>

<script>
import Block from "./Block.vue";

export default {
  inheritAttrs: false,
  components: {
    "k-builder-block": Block,
  },
  props: {
    compact: {
      type: Boolean,
      default: false
    },
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
    async add(type) {
      try {
        const block = await this.$api.get(this.endpoints.field + "/fieldsets/" + type);
        this.blocks.splice(this.nextIndex, 0, block);
        this.$refs.fieldsets.close();
        this.onInput();
        this.open(block);

      } catch (e) {
        this.$refs.fieldsets.error(e.message);
      }
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
    async duplicate(block) {
      const response = await this.$api.get(this.endpoints.field + "/uuid");
      const copy = {
        ...this.$helper.clone(block),
        id: response["uuid"]
      };
      this.blocks.push(copy);
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
        this.$refs.fieldsets.open();
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
.k-builder-fieldsets-dialog .k-headline {
  margin-bottom: .75rem;
  margin-top: -.25rem;
}
.k-builder-fieldsets {
  display: grid;
  grid-gap: .5rem;
  grid-template-columns: repeat(2, 1fr);
}
.k-builder-fieldsets .k-button {
  display: grid;
  grid-template-columns: 2rem 1fr;
  align-items: top;
  background: $color-white;
  width: 100%;
  text-align: left;
  box-shadow: $shadow;
  padding: 0 .75rem 0 0;
  line-height: 1.5em;
}
.k-builder-fieldsets .k-button .k-button-text {
  padding: .5rem 0;
}
.k-builder-fieldsets .k-button .k-icon {
  width: 38px;
  height: 38px;
}
</style>
