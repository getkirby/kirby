<template>
  <div class="k-fatal">
    <div class="k-fatal-box">
      <k-bar>
        <template #left>
          <k-headline> The JSON response could not be parsed: </k-headline>
        </template>
        <template #right>
          <k-button
            icon="cancel"
            text="Close"
            @click="$store.dispatch('fatal', false)"
          />
        </template>
      </k-bar>
      <iframe ref="iframe" class="k-fatal-iframe" />
    </div>
  </div>
</template>

<script>
/**
 * @internal
 */
export default {
  computed: {
    fatal() {
      return this.$store.state.fatal;
    }
  },
  watch: {
    fatal(html) {
      if (html !== null) {
        this.$nextTick(() => {
          try {
            let doc = this.$refs.iframe.contentWindow.document;
            doc.open();
            doc.write(html);
            doc.close();
          } catch (e) {
            console.error(e);
          }
        });
      }
    }
  }
};
</script>

<style>
.k-fatal {
  position: fixed;
  inset: 0;
  background: var(--color-backdrop);
  display: flex;
  z-index: var(--z-dialog);
  align-items: center;
  justify-content: center;
  padding: 1.5rem;
}
.k-fatal-box {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  background: var(--color-white);
  padding: 0.75rem 1.5rem 1.5rem;
  box-shadow: var(--shadow-xl);
  border-radius: var(--rounded);
}
.k-fatal-box .k-bar {
  margin-bottom: var(--spacing-3);
}
.k-fatal-iframe {
  border: 0;
  width: 100%;
  flex-grow: 1;
  border: 2px solid var(--color-border);
}
</style>
