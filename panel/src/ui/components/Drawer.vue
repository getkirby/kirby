<template>
  <k-drawer-foundation
    ref="dialog"
    v-bind="$props"
    v-on="$listeners"
  >
    <slot name="context" slot="context" />
    <slot />
  </k-drawer-foundation>
</template>

<script>
import Dialog from "./Dialog.vue";

export default {
  extends: Dialog,
  props: {
    title: {
      type: String,
      default: "Drawer"
    }
  }
}
</script>
