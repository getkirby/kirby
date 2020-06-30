<template>
  <k-dialog ref="dialog" v-bind="$props" v-on="$listeners">
    <slot>
      <k-text v-html="text" />
    </slot>
  </k-dialog>
</template>

<script>
import DialogMixin from "@/mixins/dialog.js";

export default {
  mixins: [DialogMixin],
  props: {
    text: String
  }
}
</script>
