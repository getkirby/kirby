<template>
  <k-dialog
    ref="dialog"
    :cancel-button="false"
    :submit-button="false"
    size="large"
    class="k-block-importer"
  >
    <!-- eslint-disable vue/no-v-html -->
    <label
      for="pasteboard"
      v-html="$t('field.blocks.fieldsets.paste', { shortcut })"
    />
    <!-- eslint-enable -->
    <textarea id="pasteboard" @paste.prevent="onPaste" />
  </k-dialog>
</template>

<script>
/**
 * @internal
 */
export default {
  inheritAttrs: false,
  computed: {
    shortcut() {
      return this.$helper.keyboard.metaKey() + "+v";
    }
  },
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
.k-block-importer label {
  display: block;
  padding: var(--spacing-6) var(--spacing-6) 0;
  color: var(--color-gray-400);
}
.k-block-importer label kbd {
  background: rgba(0, 0, 0, 0.5);
  font-family: var(--font-mono);
  letter-spacing: 0.1em;
  padding: 0.25rem;
  border-radius: var(--rounded);
  margin: 0 0.25rem;
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
