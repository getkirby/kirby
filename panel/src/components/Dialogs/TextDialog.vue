<template>
  <k-dialog ref="dialog" v-bind="$props" v-on="$listeners">
    <slot>
      <!-- eslint-disable-next-line vue/no-v-html -->
      <k-text v-if="text" v-html="text" />
      <k-box v-else theme="negative">
        This dialog does not define any text
      </k-box>
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
};
</script>
