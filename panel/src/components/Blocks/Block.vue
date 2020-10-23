<template>
  <div
    :class="'k-block-container-' + type"
    :data-disabled="fieldset.disabled"
    :data-hidden="isHidden"
    :data-open="isOpen"
    :data-translate="fieldset.translate"
    class="k-block-container"
    @mouseenter="isHovered = true"
    @mouseleave="isHovered = false"
  >
    <k-block-options
      v-if="isHovered"
      :isFull="isFull"
      :isHidden="isHidden"
      :isOpen="isOpen"
      v-on="$listeners"
    />
    <div :class="className" class="k-block">
      <component
        ref="editor"
        :is="customComponent"
        :isSticky="wysiwyg"
        v-bind="$props"
        v-on="$listeners"
      />
    </div>
    <k-remove-dialog ref="removeDialog" @submit="remove">
      {{ $t("field.builder.delete.confirm") }}
    </k-remove-dialog>
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
    isFull: Boolean,
    isHidden: Boolean,
    isOpen: Boolean,
    isSticky: Boolean,
    name: String,
    tabs: Object,
    type: String,
  },
  data() {
    return {
      isHovered: false
    };
  },
  computed: {
    className() {
      let className = ["k-block-" + this.type];

      if (this.fieldset.preview) {
        className.push("k-block-" + this.fieldset.preview);
      }

      if (this.wysiwyg === false) {
        className.push("k-block-default");
      }

      return className;
    },
    customComponent() {
      if (this.isOpen === true) {
        return "k-block-default";
      }

      if (this.wysiwyg) {
        return this.wysiwygComponent;
      }

      return "k-block-default";
    },
    wysiwyg() {
      return this.wysiwygComponent !== false;
    },
    wysiwygComponent() {
      let component = "k-block-" + this.type;

      if (this.$helper.isComponent(component)) {
        return component;
      }

      if (this.fieldset.preview) {
        component = "k-block-" + this.fieldset.preview;

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
      }
    },
    remove() {
      this.$refs.removeDialog.close();
      this.$emit("remove", this.id);
    }
  }
};
</script>

<style lang="scss">
.k-block-container {
  position: relative;
  padding: 0 4rem;
  border-radius: $rounded;
}
.k-block-container:focus {
  outline: 0;
}
.k-block-container .k-block-options {
  position: absolute;
  top: 50%;
  margin-top: -.75rem;
  left: .5rem;
}
.k-block-container[data-hidden] .k-block {
  opacity: .25;
}
</style>
