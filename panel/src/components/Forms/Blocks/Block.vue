<template>
  <div
    ref="container"
    :class="'k-block-container-type-' + type"
    :data-batched="isBatched"
    :data-disabled="fieldset.disabled"
    :data-hidden="isHidden"
    :data-id="id"
    :data-last-in-batch="isLastInBatch"
    :data-selected="isSelected"
    :data-translate="fieldset.translate"
    class="k-block-container"
    tabindex="0"
    @keydown.ctrl.shift.down.prevent="$emit('sortDown')"
    @keydown.ctrl.shift.up.prevent="$emit('sortUp')"
    @focus="$emit('focus')"
    @focusin="onFocusIn"
  >
    <div :class="className" class="k-block">
      <component
        :is="customComponent"
        ref="editor"
        v-bind="$props"
        v-on="listeners"
      />
    </div>

    <k-block-options
      ref="options"
      :is-batched="isBatched"
      :is-editable="isEditable"
      :is-full="isFull"
      :is-hidden="isHidden"
      v-on="listeners"
    />

    <k-form-drawer
      v-if="isEditable && !isBatched"
      :id="id"
      ref="drawer"
      :icon="fieldset.icon || 'box'"
      :tabs="tabs"
      :title="fieldset.name"
      :value="content"
      class="k-block-drawer"
      @close="focus()"
      @input="$emit('update', $event)"
    >
      <template #options>
        <k-button
          v-if="isHidden"
          class="k-drawer-option"
          icon="hidden"
          @click="$emit('show')"
        />
        <k-button
          :disabled="!prev"
          class="k-drawer-option"
          icon="angle-left"
          @click.prevent.stop="goTo(prev)"
        />
        <k-button
          :disabled="!next"
          class="k-drawer-option"
          icon="angle-right"
          @click.prevent.stop="goTo(next)"
        />
        <k-button
          class="k-drawer-option"
          icon="trash"
          @click.prevent.stop="confirmToRemove"
        />
      </template>
    </k-form-drawer>

    <k-remove-dialog
      ref="removeDialog"
      :text="$t('field.blocks.delete.confirm')"
      @submit="remove"
    />
  </div>
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    attrs: [Array, Object],
    content: [Array, Object],
    endpoints: Object,
    fieldset: Object,
    id: String,
    isBatched: Boolean,
    isFull: Boolean,
    isHidden: Boolean,
    isLastInBatch: Boolean,
    isSelected: Boolean,
    name: String,
    next: Object,
    prev: Object,
    type: String
  },
  data() {
    return {
      skipFocus: false
    };
  },
  computed: {
    className() {
      let className = ["k-block-type-" + this.type];

      if (this.fieldset.preview !== this.type) {
        className.push("k-block-type-" + this.fieldset.preview);
      }

      if (this.wysiwyg === false) {
        className.push("k-block-type-default");
      }

      return className;
    },
    customComponent() {
      if (this.wysiwyg) {
        return this.wysiwygComponent;
      }

      return "k-block-type-default";
    },
    isEditable() {
      return this.fieldset.editable !== false;
    },
    listeners() {
      return {
        ...this.$listeners,
        confirmToRemove: this.confirmToRemove,
        open: this.open
      };
    },
    tabs() {
      let tabs = this.fieldset.tabs;

      Object.entries(tabs).forEach(([tabName, tab]) => {
        Object.entries(tab.fields).forEach(([fieldName]) => {
          tabs[tabName].fields[fieldName].section = this.name;
          tabs[tabName].fields[fieldName].endpoints = {
            field:
              this.endpoints.field +
              "/fieldsets/" +
              this.type +
              "/fields/" +
              fieldName,
            section: this.endpoints.section,
            model: this.endpoints.model
          };
        });
      });

      return tabs;
    },
    wysiwyg() {
      return this.wysiwygComponent !== false;
    },
    wysiwygComponent() {
      if (this.fieldset.preview === false) {
        return false;
      }

      let component;

      // custom preview
      if (this.fieldset.preview) {
        component = "k-block-type-" + this.fieldset.preview;

        if (this.$helper.isComponent(component)) {
          return component;
        }
      }

      // default preview
      component = "k-block-type-" + this.type;

      if (this.$helper.isComponent(component)) {
        return component;
      }

      return false;
    }
  },
  methods: {
    close() {
      this.$refs.drawer.close();
    },
    confirmToRemove() {
      this.$refs.removeDialog.open();
    },
    focus() {
      if (this.skipFocus !== true) {
        if (typeof this.$refs.editor.focus === "function") {
          this.$refs.editor.focus();
        } else {
          this.$refs.container.focus();
        }
      }
    },
    onFocusIn(event) {
      // skip focus if the event is coming from the options buttons
      // to preserve the current focus (since options buttons directly
      // trigger events and don't need any focus themselves)
      if (this.$refs.options?.$el?.contains(event.target)) {
        return;
      }

      this.$emit("focus", event);
    },
    goTo(block) {
      if (block) {
        this.skipFocus = true;
        this.close();

        this.$nextTick(() => {
          block.$refs.container.focus();
          block.open();
          this.skipFocus = false;
        });
      }
    },
    open() {
      this.$refs.drawer?.open();
    },
    remove() {
      this.$refs.removeDialog.close();
      this.$emit("remove", this.id);
    }
  }
};
</script>

<style>
.k-block-container {
  position: relative;
  padding: 0.75rem;
  background: var(--color-white);
}
.k-block-container:not(:last-of-type) {
  border-bottom: 1px dashed rgba(0, 0, 0, 0.1);
}
.k-block-container:focus {
  outline: 0;
}

.k-block-container[data-batched="true"] {
  z-index: 2;
  border-bottom-color: transparent;
}
.k-block-container[data-batched="true"]::after {
  position: absolute;
  inset: 0;
  content: "";
  background: rgba(238, 242, 246, 0.375);
  mix-blend-mode: multiply;
  border: 1px solid var(--color-focus);
}

.k-block-container[data-selected="true"] {
  z-index: 2;
  box-shadow: var(--color-focus) 0 0 0 1px, var(--color-focus-outline) 0 0 0 3px;
  border-bottom-color: transparent;
}
.k-block-container .k-block-options {
  display: none;
  position: absolute;
  top: 0;
  inset-inline-end: 0.75rem;
  margin-top: calc(-1.75rem + 2px);
}
.k-block-container[data-last-in-batch="true"] > .k-block-options,
.k-block-container[data-selected="true"] > .k-block-options {
  display: flex;
}
.k-block-container[data-hidden="true"] .k-block {
  opacity: 0.25;
}
.k-drawer-options .k-button[data-disabled="true"] {
  vertical-align: middle;
  display: inline-grid;
}
[data-disabled="true"] .k-block-container {
  background: var(--color-background);
}
</style>
