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
        // TODO: $t('delete')
        return 'delete';
      }
    },
    text: String,
    theme: {
      type: String,
      default: "negative"
    },
  }
}
</script>
