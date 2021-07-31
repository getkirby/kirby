<template>
  <div class="k-fatal">
    <div class="k-fatal-box">
      <k-bar>
        <k-headline slot="left">
          The JSON response could not be parsed:
        </k-headline>
        <k-button
          slot="right"
          icon="cancel"
          text="Close"
          @click="$store.dispatch('fatal', false)"
        />
      </k-bar>
      <iframe ref="iframe" class="k-fatal-iframe" />
    </div>
  </div>
</template>

<script>
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
        })
      }
    }
  }
}
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
  padding: .75rem 1.5rem 1.5rem;
  box-shadow: var(--shadow-xl);
  border-radius: var(--rounded);
}
.k-fatal-box .k-bar {
  margin-block-end: var(--spacing-3);
}
.k-fatal-iframe {
  border: 0;
  width: 100%;
  flex-grow: 1;
  border: 2px solid var(--color-border);
}
</style>