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
      default: "trash"
    },
    submitButton: {
      default() {
        return this.$t("delete");
      }
    },
    text: String,
    theme: {
      default: "negative"
    },
  }
}
</script>
