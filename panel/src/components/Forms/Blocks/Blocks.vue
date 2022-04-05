<template>
  <div
    ref="wrapper"
    :data-empty="blocks.length === 0"
    :data-multi-select-key="isMultiSelectKey"
    class="k-blocks"
    @focusin="focussed = true"
    @focusout="focussed = false"
  >
    <template v-if="hasFieldsets">
      <k-draggable v-bind="draggableOptions" class="k-blocks-list" @sort="save">
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
          @copy="copy()"
          @confirmToRemoveSelected="confirmToRemoveSelected"
          @click.native.stop="select(block, $event)"
          @duplicate="duplicate(block, index)"
          @focus="select(block)"
          @hide="hide(block)"
          @paste="pasteboard()"
          @prepend="add($event, index)"
          @remove="remove(block)"
          @sortDown="sort(block, index, index + 1)"
          @sortUp="sort(block, index, index - 1)"
          @show="show(block)"
          @update="update(block, $event)"
        />
        <template #footer>
          <k-empty
            class="k-blocks-empty"
            icon="box"
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
        @paste="paste($event)"
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

      <k-block-pasteboard ref="pasteboard" @paste="paste($event)" />
    </template>
    <template v-else>
      <k-box theme="info"> No fieldsets yet </k-box>
    </template>
  </div>
</template>

<script>
import Pasteboard from "./BlockPasteboard.vue";

export default {
  components: {
    "k-block-pasteboard": Pasteboard
  },
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
      default: null
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
      isMultiSelectKey: false,
      batch: [],
      blocks: this.value,
      current: null,
      isFocussed: false
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
      return (
        this.$store.state.dialog || this.$store.state.drawers.open.length > 0
      );
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
      return this.current;
    },
    selectedOrBatched() {
      if (this.batch.length > 0) {
        return this.batch;
      }

      if (this.selected) {
        return [this.selected];
      }

      return [];
    }
  },
  watch: {
    value() {
      this.blocks = this.value;
    }
  },
  created() {
    this.$events.$on("copy", this.copy);
    this.$events.$on("focus", this.onOutsideFocus);
    this.$events.$on("keydown", this.onKey);
    this.$events.$on("keyup", this.onKey);
    this.$events.$on("paste", this.onPaste);
  },
  destroyed() {
    this.$events.$off("copy", this.copy);
    this.$events.$off("focus", this.onOutsideFocus);
    this.$events.$off("keydown", this.onKey);
    this.$events.$off("keyup", this.onKey);
    this.$events.$off("paste", this.onPaste);
  },
  mounted() {
    // focus first block
    if (this.$props.autofocus === true) {
      this.focus();
    }
  },
  methods: {
    append(what, index) {
      if (typeof what === "string") {
        this.add(what, index);
        return;
      }

      if (Array.isArray(what)) {
        let blocks = this.$helper.clone(what).map((block) => {
          block.id = this.$helper.uuid();
          return block;
        });

        // filters only supported blocks
        const availableFieldsets = Object.keys(this.fieldsets);
        blocks = blocks.filter((block) =>
          availableFieldsets.includes(block.type)
        );

        // don't add blocks that exceed the maximum limit
        if (this.max) {
          const max = this.max - this.blocks.length;
          blocks = blocks.slice(0, max);
        }

        this.blocks.splice(index, 0, ...blocks);
        this.save();
      }
    },
    async add(type = "text", index) {
      const block = await this.$api.get(
        this.endpoints.field + "/fieldsets/" + type
      );
      this.blocks.splice(index, 0, block);
      this.save();

      this.$nextTick(() => {
        this.focusOrOpen(block);
      });
    },
    addToBatch(block) {
      // move the selected block to the batch first
      if (
        this.selected !== null &&
        this.batch.includes(this.selected) === false
      ) {
        this.batch.push(this.selected);
        this.current = null;
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
    copy(e) {
      // don't copy when the drawer is open
      if (this.isEditing === true) {
        return false;
      }

      // don't copy when there are no blocks yet
      if (this.blocks.length === 0) {
        return false;
      }

      // don't copy when nothing is selected
      if (this.selectedOrBatched.length === 0) {
        return false;
      }

      // don't copy if an input is focused
      if (this.isInputEvent(e) === true) {
        return false;
      }

      let blocks = [];

      this.blocks.forEach((block) => {
        if (this.selectedOrBatched.includes(block.id)) {
          blocks.push(block);
        }
      });

      // don't copy if no blocks could be found
      if (blocks.length === 0) {
        return false;
      }

      this.$helper.clipboard.write(blocks, e);

      if (e instanceof ClipboardEvent === false) {
        // reselect the previously focussed elements
        this.batch = this.selectedOrBatched;
      }

      // a sign that it has been copied
      this.$store.dispatch("notification/success", `${blocks.length} copied!`);
    },
    copyAll() {
      this.selectAll();
      this.copy();
      this.deselectAll();
    },
    async convert(type, block) {
      const index = this.findIndex(block.id);

      if (index === -1) {
        return false;
      }

      const fields = (fieldset) => {
        let fields = {};

        for (const tab of Object.values(fieldset?.tabs ?? {})) {
          fields = {
            ...fields,
            ...tab.fields
          };
        }

        return fields;
      };

      const oldBlock = this.blocks[index];
      const newBlock = await this.$api.get(
        this.endpoints.field + "/fieldsets/" + type
      );

      const oldFieldset = this.fieldsets[oldBlock.type];
      const newFieldset = this.fieldsets[type];

      if (!newFieldset) {
        return false;
      }

      let content = newBlock.content;

      const newFields = fields(newFieldset);
      const oldFields = fields(oldFieldset);

      for (const [name, field] of Object.entries(newFields)) {
        const oldField = oldFields[name];

        if (oldField?.type === field.type && oldBlock?.content?.[name]) {
          content[name] = oldBlock.content[name];
        }
      }

      this.blocks[index] = {
        ...newBlock,
        id: oldBlock.id,
        content: content
      };

      this.save();
    },
    deselectAll() {
      this.batch = [];
      this.current = null;
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
      return (
        this.fieldsets[block.type] || {
          icon: "box",
          name: block.type,
          tabs: {
            content: {
              fields: {}
            }
          },
          type: block.type
        }
      );
    },
    find(id) {
      return this.blocks.find((element) => element.id === id);
    },
    findIndex(id) {
      return this.blocks.findIndex((element) => element.id === id);
    },
    focus(block) {
      if (block?.id && this.$refs["block-" + block.id]) {
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
    isInputEvent() {
      const focused = document.querySelector(":focus");
      return (
        focused &&
        focused.matches("input, textarea, [contenteditable], .k-writer")
      );
    },
    isLastInBatch(block) {
      const [lastItem] = this.batch.slice(-1);
      return lastItem && block.id === lastItem;
    },
    isOnlyInstance() {
      return document.querySelectorAll(".k-blocks").length === 1;
    },
    isSelected(block) {
      return this.selected && this.selected === block.id;
    },
    move(event) {
      // moving block between fields
      if (event.from !== event.to) {
        const block = event.draggedContext.element;
        const to =
          event.relatedContext.component.componentData ||
          event.relatedContext.component.$parent.componentData;

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
    onKey(event) {
      this.isMultiSelectKey = event.metaKey || event.ctrlKey || event.altKey;
    },
    onOutsideFocus(event) {
      // ignore focus in dialogs to not alter current selection
      if (
        typeof event.target.closest === "function" &&
        event.target.closest(".k-dialog")
      ) {
        return;
      }

      const overlay = document.querySelector(".k-overlay:last-of-type");
      if (
        this.$el.contains(event.target) === false &&
        (!overlay || overlay.contains(event.target) === false)
      ) {
        return this.select(null);
      }

      // since we are still working in the same block when overlay is open
      // we cannot detect the transition between the layout columns
      // following codes detect if the target is in the same column
      if (overlay) {
        const layoutColumn = this.$el.closest(".k-layout-column");
        if (layoutColumn?.contains(event.target) === false) {
          return this.select(null);
        }
      }
    },
    onPaste(e) {
      // never paste blocks when the focus is in an input element
      if (this.isInputEvent(e) === true) {
        return false;
      }

      // never paste when dialogs or drawers are open
      if (this.isEditing === true) {
        // enable pasting when the block selector is open
        if (this.$refs.selector?.isOpen() === true) {
          return this.paste(e);
        }

        return false;
      }

      // if nothing is selected …
      if (this.selectedOrBatched.length === 0) {
        // if there are multiple instances,
        // pasting is disabled to avoid multiple
        // pasted blocks
        if (this.isOnlyInstance() !== true) {
          return false;
        }
      }

      return this.paste(e);
    },
    open(block) {
      if (this.$refs["block-" + block.id]) {
        this.$refs["block-" + block.id][0].open();
      }
    },
    async paste(e) {
      const html = this.$helper.clipboard.read(e);

      // pass html or plain text to the paste endpoint to convert it to blocks
      const blocks = await this.$api.post(this.endpoints.field + "/paste", {
        html: html
      });

      // get the index
      let lastItem = this.selectedOrBatched[this.selectedOrBatched.length - 1];
      let lastIndex = this.findIndex(lastItem);

      if (lastIndex === -1) {
        lastIndex = this.blocks.length;
      }

      this.append(blocks, lastIndex + 1);
    },
    pasteboard() {
      this.$refs.pasteboard.open();
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
        if (this.selected?.id === block.id) {
          this.select(null);
        }

        this.$delete(this.blocks, index);
        this.save();
      }
    },
    removeAll() {
      this.batch = [];
      this.blocks = [];
      this.save();
      this.$refs.removeAll.close();
    },
    removeSelected() {
      this.batch.forEach((id) => {
        const index = this.findIndex(id);
        if (index !== -1) {
          this.$delete(this.blocks, index);
        }
      });

      this.deselectAll();
      this.save();
      this.$refs.removeSelected.close();
    },
    save() {
      this.$emit("input", this.blocks);
    },
    select(block, event = null) {
      // checks the event just before selecting the block
      // especially since keyup doesn't trigger in with
      // `ctrl/alt/cmd + tab` or `ctrl/alt/cmd + click` combinations
      // for ex: clicking outside of webpage or another browser tab
      if (event && this.isMultiSelectKey) {
        this.onKey(event);
      }

      if (block && this.isMultiSelectKey) {
        this.addToBatch(block);
        this.current = null;
        return;
      }

      this.batch = [];
      this.current = block ? block.id : null;
    },
    selectAll() {
      this.batch = Object.values(this.blocks).map((block) => block.id);
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
[data-disabled="true"] .k-blocks {
  background: var(--color-background);
}
.k-blocks[data-multi-select-key="true"] .k-block-container > * {
  pointer-events: none;
}
.k-blocks[data-empty="true"] {
  padding: 0;
  background: none;
  box-shadow: none;
}
.k-blocks .k-sortable-ghost {
  outline: 2px solid var(--color-focus);
  box-shadow: rgba(17, 17, 17, 0.25) 0 5px 10px;
  cursor: grabbing;
  cursor: -moz-grabbing;
  cursor: -webkit-grabbing;
}
.k-blocks-list > .k-blocks-empty {
  display: flex;
  align-items: center;
}
.k-blocks-list > .k-blocks-empty:not(:only-child) {
  display: none;
}
</style>
