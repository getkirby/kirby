<template>
  <k-text-dialog ref="dialog" v-bind="$props" v-on="$listeners">
    <slot />
  </k-text-dialog>
</template>

<script>
import { TranslationString } from '@/config/i18n.js'
import DialogMixin from "@/mixins/dialog.js";

export default {
  mixins: [DialogMixin],
  props: {
    icon: {
      type: String,
      default: "trash"
    },
    submitButton: {
      type: [String, TranslationString, Boolean],
      default() { 
        return new TranslationString("delete")
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
