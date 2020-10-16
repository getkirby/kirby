<template>
  <div
    :data-compact="compact"
    :data-disabled="fieldset.disabled"
    :data-hidden="isHidden"
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
      v-on="listeners"
    />
    <component
      :is="customComponentName"
      v-bind="$props"
      class="k-block"
      v-on="listeners"
    />
    <k-remove-dialog ref="removeDialog" @submit="remove">
      {{ $t("field.builder.delete.confirm") }}
    </k-remove-dialog>
  </div>
</template>

<script>
import Vue from "vue";
import Editor from "./Editor.vue";
import BlockForm from "./BlockForm.vue";
import BlockSwitcher from "./BlockSwitcher.vue";
import BlockHeader from "./BlockHeader.vue";
import BlockOptions from "./BlockOptions.vue";

Vue.component("k-editor", Editor);

Vue.component("k-block-form", BlockForm);
Vue.component("k-block-header", BlockHeader);
Vue.component("k-block-options", BlockOptions);
Vue.component("k-block-switcher", BlockSwitcher);

import Generic from "./Blocks/Generic.vue";
import Heading from "./Blocks/Heading.vue";
import Quote from "./Blocks/Quote.vue";
import Text from "./Blocks/Text.vue";

Vue.component("k-block-generic", Generic);
Vue.component("k-block-heading", Heading);
Vue.component("k-block-quote", Quote);
Vue.component("k-block-text", Text);

import CtaPreview from "./Previews/Cta.vue";
import ImagesPreview from "./Previews/Images.vue";
import VideoPreview from "./Previews/Video.vue";

Vue.component("k-block-cta-preview", CtaPreview);
Vue.component("k-block-images-preview", ImagesPreview);
Vue.component("k-block-video-preview", VideoPreview);

export default {
  inheritAttrs: false,
  props: {
    attrs: [Array, Object],
    compact: Boolean,
    content: [Array, Object],
    endpoints: Object,
    fieldset: Object,
    id: String,
    isFull: Boolean,
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
    customComponentName() {
      let customComponentName = "k-block-" + this.type;

      if (this.$helper.isComponent(customComponentName)) {
        return customComponentName;
      }

      if (this.$helper.isComponent("k-block-" + this.type + "-preview")) {
        return "k-block-switcher";
      }

      return "k-block-generic";
    },
    isHidden() {
      return this.attrs.hide === true;
    },
    listeners() {
      return {
        ...this.$listeners,
        remove: this.confirmToRemove
      }
    }
  },
  methods: {
    confirmToRemove() {
      this.$refs.removeDialog.open();
    },
    open() {

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
  padding: .5rem 4rem;
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
