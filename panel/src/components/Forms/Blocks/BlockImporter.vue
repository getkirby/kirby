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
  props: {
    endpoint: String,
  },
  data() {
    return {
      fields: {
        html: {
          label: "Paste HTML, text or JSON to create blocks …",
          type: "textarea",
          size: "large",
          buttons: false
        }
      },
    }
  },
  methods: {
    close() {
      this.$refs.dialog.close();
    },
    open() {
      this.$refs.dialog.open();
    },
    async onPaste(clipboardEvent) {
      const html = clipboardEvent.clipboardData.getData("text/html") || clipboardEvent.clipboardData.getData("text/plain") || null;

      // pass html or plain text to the paste endpoint to convert it to blocks
      const blocks = await this.$api.post(this.endpoint + "/paste", { html: html });

      this.$emit("paste", blocks);
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
