<template>
  <div
    ref="container"
    :class="'k-block-container-type-' + type"
    :data-batched="isBatched"
    :data-disabled="fieldset.disabled"
    :data-hidden="isHidden"
    :data-last-in-batch="isLastInBatch"
    :data-selected="isSelected"
    :data-translate="fieldset.translate"
    class="k-block-container"
    tabindex="0"
    @keydown.ctrl.shift.down.prevent="$emit('sortDown')"
    @keydown.ctrl.shift.up.prevent="$emit('sortUp')"
    @focus="$emit('focus')"
    @focusin="$emit('focus')"
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
      :is-full="isFull"
      :is-hidden="isHidden"
      v-on="listeners"
    />

    <k-form-drawer
      v-if="!isBatched"
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
    type: String,
  },
  computed: {
    className() {
      let className = ["k-block-type-" + this.type];

      if (this.fieldset.preview && this.fieldset.preview !== this.type) {
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
    listeners() {
      return {
        ...this.$listeners,
        confirmToRemove: this.confirmToRemove,
        open: this.open,
      }
    },
    tabs() {
      let tabs = this.fieldset.tabs;

      Object.entries(tabs).forEach(([tabName, tab]) => {
        Object.entries(tab.fields).forEach(([fieldName]) => {

          tabs[tabName].fields[fieldName].section = this.name;
          tabs[tabName].fields[fieldName].endpoints = {
            field:   this.endpoints.field + "/fieldsets/" + this.type + "/fields/" + fieldName,
            section: this.endpoints.section,
            model:   this.endpoints.model
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

      let component = "k-block-type-" + this.type;

      if (this.$helper.isComponent(component)) {
        return component;
      }

      if (this.fieldset.preview) {
        component = "k-block-type-" + this.fieldset.preview;

        if (this.$helper.isComponent(component)) {
          return component;
        }
      }

      return false;
    },
  },
  methods: {
    confirmToRemove() {
      this.$refs.removeDialog.open();
    },
    focus() {
      if (typeof this.$refs.editor.focus === "function") {
        this.$refs.editor.focus();
      } else {
        this.$refs.container.focus();
      }
    },
    open() {
      this.$refs.drawer.open();
    },
    remove() {
      this.$refs.removeDialog.close();
      this.$emit("remove", this.id);
    },
  }
};
</script>

<style lang="scss">
.k-block-container {
  position: relative;
  padding: .75rem;
  border-bottom: 1px dashed rgba(#000, .1);
  background: $color-white;
}
.k-block-container:last-of-type {
  border-bottom: 0;
}
.k-block-container:focus {
  outline: 0;
}

.k-block-container[data-batched] {
  z-index: 2;
  border-bottom-color: transparent;
}
.k-block-container[data-batched]::after {
  position: absolute;
  content: "";
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  background: rgba($color-blue-200, .375);
  mix-blend-mode: multiply;
  border: 1px solid $color-focus;
}

.k-block-container[data-selected] {
  z-index: 2;
  box-shadow: $color-focus 0 0 0 1px, $color-focus-outline 0 0 0 3px;
  border-bottom-color: transparent;
}
.k-block-container .k-block-options {
  position: absolute;
  top: 0;
  right: .75rem;
  margin-top: calc(-1.75rem + 2px);
  display: none;
}
.k-block-container[data-last-in-batch] .k-block-options,
.k-block-container[data-selected] .k-block-options {
  display: block;
}
.k-block-container[data-hidden] .k-block {
  opacity: .25;
}
</style>
