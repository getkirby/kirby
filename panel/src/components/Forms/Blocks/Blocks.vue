<template>
  <div
    ref="wrapper"
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
          @append="append($event, index + 1)"
          @blur="select(null)"
          @choose="choose($event)"
          @chooseToAppend="choose(index + 1)"
          @chooseToConvert="chooseToConvert(block)"
          @chooseToPrepend="choose(index)"
          @confirmToRemoveSelected="confirmToRemoveSelected"
          @click.native.stop
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
    autofocus: Boolean,
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
        handle: ".k-sort-handle",
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
    isEditing() {
      return this.$store.state.dialog || this.$store.state.drawers.open.length > 0;
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
    },
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

    document.addEventListener("copy", this.copyToClipboard, true);
    document.addEventListener("focus", this.outsideFocus, true);
    document.addEventListener("paste", this.pasteFromClipboard, true);

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
    document.removeEventListener("copy", this.copyToClipboard, true);
    document.removeEventListener("focus", this.outsideFocus);
    document.removeEventListener("keydown", this.onAlt);
    document.removeEventListener("keyup", this.onAlt);
    document.removeEventListener("paste", this.pasteFromClipboard);
  },
  mounted() {
    // focus first wysiwyg block if autofocus enabled
    if (this.$props.autofocus === true) {
      let skipFocus = false;

      Object.values(this.blocks).forEach(block => {
        if (skipFocus === false) {
          let fieldset = this.fieldset(block);

          if (fieldset.wysiwyg === true) {
            skipFocus = true;
            setTimeout(() => {
              this.focus(block);
            }, 1);
          }
        }
      });
    }
  },
  methods: {
    append(what, index) {
      if (typeof what === "string") {
        this.add(what, index);
        return;
      }

      if (Array.isArray(what)) {
        this.blocks.splice(index, 0, ...what);
        this.save();
      }
    },
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
      const index = this.findIndex(block.id);

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
    async copyToClipboard(clipboardEvent) {
      if (this.isEditing) {
        return false;
      }

      // get selected blocks
      let selected = this.batch.length === 0 ? [this.selected] : this.batch;

      // when there are blocks, copy should only
      // be possible when a block is active and
      // the cursor is not in a text area
      if (this.blocks.length > 0) {

        // only copy if something is selected
        if (selected.length === 0) {
          return false;
        }

        // only copy if the cursor is not in a text field
        if (clipboardEvent.target.closest('.k-writer, input, textarea, [contenteditable]')) {
          return false;
        }
      }

      clipboardEvent.preventDefault();

      let json = [];

      selected.forEach(id => {
        const block = this.find(id);
        if (block) {
          json.push(block);
        }
      });

      json = JSON.stringify(json, null, 2);

      clipboardEvent.clipboardData.setData("text/plain", json);
      clipboardEvent.clipboardData.setData("application/vnd.kirby.blocks", json);
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
    find(id) {
      return this.blocks.find(element => element.id === id);
    },
    findIndex(id) {
      return this.blocks.findIndex(element => element.id === id);
    },
    focus(block) {
      if (block && block.id && this.$refs["block-" + block.id]) {
        this.$refs["block-" + block.id][0].focus();
        return;
      }

      if (this.blocks[0]) {
        this.focus(this.blocks[0]);
        return;
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
    pasteFromClipboard(clipboardEvent)
    {
      // only paste if no drawer or dialog is open
      if (this.isEditing === true) {
        if (this.$refs.selector.isOpen() === false) {
          return false;
        }
      }

      clipboardEvent.preventDefault();

      let blocks = null;

      // try to fetch blocks from the clipboard
      try {
        blocks = JSON.parse(clipboardEvent.clipboardData.getData("application/vnd.kirby.blocks"));
      } catch (e) {
        blocks = null;
      }

      // get regular HTML or plain text content from the clipboard
      if (blocks === null) {
        blocks = clipboardEvent.clipboardData.getData("text/html") || clipboardEvent.clipboardData.getData("text/plain") || null;
      }

      // when there are no blocks, pasting should only
      // be possible when the selector is open
      if (this.blocks.length === 0) {
        if (this.$refs.selector.isOpen() === false) {
          return false;
        }

      // when there are blocks, pasting should only
      // be possible when a block is active and
      // the cursor is not in a text area
      } else {

        // only paste if something is selected and there are blocks
        if (!this.selected && this.batch.length === 0 && blocks === null) {
          return false;
        }

        // only paste if the cursor is not in a text field
        if (clipboardEvent.target.closest('.k-writer, input, textarea, [contenteditable]')) {
          return false;
        }
      }

      this.paste(blocks);
    },
    async paste(input) {

      let blocks = [];

      // if blocks are already passed as an array of objects
      // we can import them directly without sending them to the API
      if (Array.isArray(input)) {
        blocks = input.map(block => {
          // each block needs a new unique id to avoid collisions
          block.id = this.$helper.uuid();
          return block;
        });
      } else {
        // pass html or plain text to the paste endpoint to convert it to blocks
        blocks = await this.$api.post(this.endpoints.field + "/paste", { html: input });
      }

      // filters only supported blocks
      const availableFieldsets = Object.keys(this.fieldsets);
      blocks = blocks.filter(block => availableFieldsets.includes(block.type));

      if (this.selected !== null) {
        const index = this.findIndex(this.selected)
        this.blocks.splice(index + 1, 0, ...blocks);
      } else {
        this.blocks.push(...blocks);
      }

      this.save();
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
      const index = this.findIndex(block.id);

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
        const index = this.findIndex(id);
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
      const index = this.findIndex(block.id);
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
