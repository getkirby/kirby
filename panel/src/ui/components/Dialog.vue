<template>
  <k-dialog-foundation
    ref="dialog"
    v-bind="{...$props, ...$attrs}"
    v-on="$listeners"
  >
    <slot />

    <template v-slot:footer>
      <slot name="footer" />
    </template>
  </k-dialog-foundation>
</template>

<script>
import DialogFoundation from "./DialogFoundation.vue";

export default {
  props: DialogFoundation.props,
  methods: {
    cancel() {
      this.$refs.dialog.cancel();
    },
    close() {
      this.$refs.dialog.close();
    },
    error(message) {
      this.$refs.dialog.error(message);
    },
    open() {
      this.$refs.dialog.open();
    },
    preload() {
      this.$refs.dialog.preload();
    },
    submit() {
      this.$refs.dialog.submit();
    },
    success(message) {
      this.$refs.dialog.success(message);
    }
  }
};
</script>
