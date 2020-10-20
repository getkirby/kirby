<template>
  <div
    :class="'k-block-container-' + type"
    :data-disabled="fieldset.disabled"
    :data-hidden="isHidden"
    :data-open="isOpen"
    :data-translate="fieldset.translate"
    class="k-block-container"
    tabindex="0"
    @mouseenter="isHovered = true"
    @mouseleave="isHovered = false"
  >
    <k-block-options
      v-if="isHovered"
      :is-full="isFull"
      :is-hidden="isHidden"
      :is-open="isOpen"
      v-on="$listeners"
    />
    <div :class="className" class="k-block">
      <component
        ref="editor"
        :is="customComponent"
        :sticky="wysiwyg"
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
import Vue from "vue";
import BlockForm from "./BlockForm.vue";
import BlockHeader from "./BlockHeader.vue";
import BlockOptions from "./BlockOptions.vue";

Vue.component("k-block-form", BlockForm);
Vue.component("k-block-header", BlockHeader);
Vue.component("k-block-options", BlockOptions);

import Code from "./Blocks/Code.vue";
import Cta from "./Blocks/Cta.vue";
import Generic from "./Blocks/Generic.vue";
import Heading from "./Blocks/Heading.vue";
import Image from "./Blocks/Image.vue";
import Images from "./Blocks/Images.vue";
import Quote from "./Blocks/Quote.vue";
import Table from "./Blocks/Table.vue";
import Text from "./Blocks/Text.vue";
import Video from "./Blocks/Video.vue";

Vue.component("k-block-code", Code);
Vue.component("k-block-cta", Cta);
Vue.component("k-block-generic", Generic);
Vue.component("k-block-heading", Heading);
Vue.component("k-block-image", Image);
Vue.component("k-block-images", Images);
Vue.component("k-block-quote", Quote);
Vue.component("k-block-table", Table);
Vue.component("k-block-text", Text);
Vue.component("k-block-video", Video);

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

      if (this.wysiwyg === false) {
        className.push("k-block-generic");
      }

      return className;
    },
    customComponent() {
      if (this.isOpen === true) {
        return "k-block-generic";
      }

      if (this.wysiwyg) {
        return this.wysiwygComponent;
      }

      return "k-block-generic";
    },
    wysiwyg() {
      return this.$helper.isComponent(this.wysiwygComponent);
    },
    wysiwygComponent() {
      return "k-block-" + this.type;
    },
  },
  methods: {
    confirmToRemove() {
      this.$refs.removeDialog.open();
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
.k-blocks .k-block-container:first-child .k-block {
  padding-top: 0;
}
.k-blocks .k-block-container:last-of-type .k-block {
  padding-bottom: 0;
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
