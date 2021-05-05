<template>
  <div
    :data-empty="blocks.length === 0"
    :data-alt="altKey"
    class="k-blocks"
  >
    <template v-if="hasFieldsets">
      <k-draggable
        v-bind="draggableOptions"
        class="k-blocks-list"
        @sort="save"
      >
        <k-block
          v-for="(block, index) in blocks"
          :ref="'block-' + block.id"
          :key="block.id"
          :endpoints="endpoints"
          :fieldset="fieldset(block)"
          :is-batched="isBatched(block)"
          :is-last-in-batch="isLastInBatch(block)"
          :is-full="isFull"
          :is-hidden="block.isHidden === true"
          :is-selected="isSelected(block)"
          :next="prevNext(index + 1)"
          :prev="prevNext(index - 1)"
          v-bind="block"
          @append="add($event, index + 1)"
          @blur="select(null)"
          @choose="choose($event)"
          @chooseToAppend="choose(index + 1)"
          @chooseToConvert="chooseToConvert(block)"
          @chooseToPrepend="choose(index)"
          @confirmToRemoveSelected="confirmToRemoveSelected"
          @duplicate="duplicate(block, index)"
          @focus="select(block)"
          @hide="hide(block)"
          @prepend="add($event, index)"
          @remove="remove(block)"
          @sortDown="sort(block, index, index + 1)"
          @sortUp="sort(block, index, index - 1)"
          @show="show(block)"
          @update="update(block, $event)"
        />
        <template #footer>
          <k-empty
            icon="box"
            class="k-blocks-empty"
            @click="choose(blocks.length)"
          >
            {{ empty || $t("field.blocks.empty") }}
          </k-empty>
        </template>
      </k-draggable>

      <k-block-selector
        ref="selector"
        :fieldsets="fieldsets"
        :fieldset-groups="fieldsetGroups"
        @add="add"
        @convert="convert"
      />

      <k-remove-dialog
        ref="removeAll"
        :text="$t('field.blocks.delete.confirm.all')"
        @submit="removeAll"
      />

      <k-remove-dialog
        ref="removeSelected"
        :text="$t('field.blocks.delete.confirm.selected')"
        @submit="removeSelected"
      />
    </template>
    <template v-else>
      <k-box theme="info">
        No fieldsets yet
      </k-box>
    </template>
  </div>
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    empty: String,
    endpoints: Object,
    fieldsets: Object,
    fieldsetGroups: Object,
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
      batch: [],
      blocks: this.value,
      altKey: false,
    };
  },
  computed: {
    draggableOptions() {
      return {
        id: this._uid,
        handle: ".k-block-handle",
        list: this.blocks,
        move: this.move,
        delay: 10,
        data: {
          fieldsets: this.fieldsets,
          isFull: this.isFull
        },
        options: {
          group: this.group
        }
      };
    },
    hasFieldsets() {
      return Object.keys(this.fieldsets).length;
    },
    isEmpty() {
      return this.blocks.length === 0;
    },
    isFull() {
      if (this.max === null) {
        return false;
      }

      return this.blocks.length >= this.max;
    },
    selected() {
      return this.$store.state.blocks.current;
    }
  },
  watch: {
    value() {
      this.blocks = this.value;
    }
  },
  created() {
    this.outsideFocus = (event) => {
      const overlay = document.querySelector(".k-overlay:last-of-type");
      if (this.$el.contains(event.target) === false && (!overlay || overlay.contains(event.target) === false)) {
        this.select(null);
      }
    };

    document.addEventListener("focus", this.outsideFocus, true);

    this.onAlt = (event) => {
      if (event.altKey) {
        this.altKey = true;
      } else {
        this.altKey = false;
      }
    };

    document.addEventListener("keydown", this.onAlt, true);
    document.addEventListener("keyup", this.onAlt, true);
  },
  destroyed() {
    document.removeEventListener("focus", this.outsideFocus);
    document.removeEventListener("keydown", this.onAlt);
    document.removeEventListener("keyup", this.onAlt);
  },
  methods: {
    async add(type = "text", index) {
      const block = await this.$api.get(this.endpoints.field + "/fieldsets/" + type);
      this.blocks.splice(index, 0, block);
      this.save();

      this.$nextTick(() => {
        this.focusOrOpen(block);
      });
    },
    addToBatch(block) {

      // move the selected block to the batch first
      if (this.selected !== null && this.batch.includes(this.selected) === false) {
        this.batch.push(this.selected);
        this.$store.dispatch("blocks/current", null);
      }

      if (this.batch.includes(block.id) === false) {
        this.batch.push(block.id);
      }
    },
    choose(index) {
      if (Object.keys(this.fieldsets).length === 1) {
        const type = Object.values(this.fieldsets)[0].type;
        this.add(type, index);
      } else {
        this.$refs.selector.open(index);
      }
    },
    chooseToConvert(block) {
      this.$refs.selector.open(block, {
        disabled: [block.type],
        headline: this.$t("field.blocks.changeType"),
        event: "convert"
      });
    },
    click(block) {
      this.$emit("click", block);
    },
    confirmToRemoveAll() {
      this.$refs.removeAll.open();
    },
    confirmToRemoveSelected() {
      this.$refs.removeSelected.open();
    },
    async convert(type, block) {
      const index = this.blocks.findIndex(element => element.id === block.id);

      if (index === -1) {
        return false;
      }

      const fields = (fieldset) => {
        let fields = {};
        Object.values(fieldset.tabs).forEach(tab => {
          fields = {
            ...fields,
            ...tab.fields
          };
        });

        return fields;
      };

      const oldBlock = this.blocks[index];
      const newBlock = await this.$api.get(this.endpoints.field + "/fieldsets/" + type);

      const oldFieldset = this.fieldsets[oldBlock.type];
      const newFieldset = this.fieldsets[type];

      if (!newFieldset) {
        return false;
      }

      let content = newBlock.content;

      const oldFields = fields(oldFieldset);
      const newFields = fields(newFieldset);

      Object.entries(newFields).forEach(([name, field]) => {
        const oldField = oldFields[name];

        if (oldField && oldField.type === field.type && oldBlock.content[name]) {
          content[name] = oldBlock.content[name];
        }
      });

      this.blocks[index] = {
        ...newBlock,
        id: oldBlock.id,
        content: content
      };

      this.save();
    },
    async duplicate(block, index) {
      const copy = {
        ...this.$helper.clone(block),
        id: this.$helper.uuid()
      };
      this.blocks.splice(index + 1, 0, copy);
      this.save();
    },
    fieldset(block) {
      return this.fieldsets[block.type] || {
        icon: "box",
        name: block.type,
        tabs: {
          content: {
            fields: {}
          }
        },
        type: block.type,
      };
    },
    focus(block) {
      if (this.$refs["block-" + block.id]) {
        this.$refs["block-" + block.id][0].focus();
      }
    },
    focusOrOpen(block) {
      if (this.fieldsets[block.type].wysiwyg) {
        this.focus(block);
      } else {
        this.open(block);
      }
    },
    hide(block) {
      this.$set(block, "isHidden", true);
      this.save();
    },
    isBatched(block) {
      return this.batch.includes(block.id);
    },
    isLastInBatch(block) {
      const [lastItem] = this.batch.slice(-1);
      return lastItem && block.id === lastItem;
    },
    isSelected(block) {
      return this.selected && this.selected === block.id;
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
    open(block) {
      if (this.$refs["block-" + block.id]) {
        this.$refs["block-" + block.id][0].open();
      }
    },
    prevNext(index) {
      if (this.blocks[index]) {
        let block = this.blocks[index];

        if (this.$refs["block-" + block.id]) {
          return this.$refs["block-" + block.id][0];
        }
      }
    },
    remove(block) {
      const index = this.blocks.findIndex(element => element.id === block.id);

      if (index !== -1) {

        if (this.selected && this.selected.id === block.id) {
          this.select(null);
        }

        this.$delete(this.blocks, index);
        this.save();
      }
    },
    removeAll() {
      this.blocks = [];
      this.save();
      this.$refs.removeAll.close();
    },
    removeSelected() {
      this.batch.forEach(id => {
        const index = this.blocks.findIndex(element => element.id === id);
        if (index !== -1) {
          this.$delete(this.blocks, index);
        }
      });

      this.batch = [];
      this.$store.dispatch("blocks/current", null);
      this.save();
      this.$refs.removeSelected.close();
    },
    save() {
      this.$emit("input", this.blocks);
    },
    select(block) {
      if (block && this.altKey) {
        this.addToBatch(block);
        return;
      }

      this.batch = [];
      this.$store.dispatch("blocks/current", block ? block.id : null);
    },
    show(block) {
      this.$set(block, "isHidden", false);
      this.save();
    },
    sort(block, from, to) {
      if (to < 0) {
        return;
      }
      let blocks = this.$helper.clone(this.blocks);
      blocks.splice(from, 1);
      blocks.splice(to, 0, block);
      this.blocks = blocks;
      this.save();
      this.$nextTick(() => {
        this.focus(block);
      });
    },
    update(block, content) {
      const index = this.blocks.findIndex(element => element.id === block.id);
      if (index !== -1) {

        Object.entries(content).forEach(([key, value]) => {
          this.$set(this.blocks[index].content, key, value);
        });

      }
      this.save();
    }
  }
};
</script>

<style>
.k-blocks {
  background: var(--color-white);
  box-shadow: var(--shadow);
  border-radius: var(--rounded);
}
[data-disabled] .k-blocks {
  background: var(--color-background);
}
.k-blocks[data-alt] .k-block-container > * {
  pointer-events: none;
}
.k-blocks[data-empty] {
  padding: 0;
  background: none;
  box-shadow: none;
}
.k-blocks .k-sortable-ghost {
  outline: 2px solid var(--color-focus);
  box-shadow: rgba(17, 17, 17, .25) 0 5px 10px;
  cursor: grabbing;
  cursor: -moz-grabbing;
  cursor: -webkit-grabbing;
}
.k-blocks-empty.k-empty {
  cursor: pointer;
  display: flex;
  align-items: center;
}
.k-blocks-list > .k-blocks-empty:not(:only-child) {
  display: none;
}
</style>
