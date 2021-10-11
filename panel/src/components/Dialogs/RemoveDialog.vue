<template>
  <k-text-dialog ref="dialog" v-bind="$props" v-on="$listeners">
    <slot />
  </k-text-dialog>
</template>

<script>
import DialogMixin from "@/mixins/dialog.js";

export default {
  mixins: [DialogMixin],
  props: {
    icon: {
      type: String,
      default: "trash"
    },
    submitButton: {
      type: [String, Boolean],
      default() {
        return window.panel.$t("delete");
      }
    },
    text: String,
    theme: {
      type: String,
      default: "negative"
    }
  }
};
</script>
