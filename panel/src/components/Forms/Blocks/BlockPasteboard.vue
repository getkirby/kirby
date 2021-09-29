<template>
  <k-dialog
    ref="dialog"
    :cancel-button="false"
    :submit-button="false"
    size="large"
    class="k-block-importer"
  >
    <textarea placeholder="Paste text or HTML here to create blocks …" @paste.prevent="onPaste" />
  </k-dialog>
</template>

<script>
/**
 * @internal
 */
export default {
  inheritAttrs: false,
  methods: {
    close() {
      this.$refs.dialog.close();
    },
    open() {
      this.$refs.dialog.open();
    },
    onPaste(clipboardEvent) {
      this.$emit("paste", clipboardEvent);
      this.close();
    }
  }
};
</script>

<style>
.k-block-importer.k-dialog {
  background: #313740;
  color: var(--color-white);
}
.k-block-importer .k-dialog-body {
  padding: 0;
}
.k-block-importer textarea {
  width: 100%;
  height: 20rem;
  background: none;
  font: inherit;
  color: var(--color-white);
  border: 0;
  padding: var(--spacing-6);
  resize: none;
}
.k-block-importer textarea:focus {
  outline: 0;
}
</style>
