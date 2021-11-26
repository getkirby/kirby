<template>
  <div class="k-fatal">
    <div class="k-fatal-box">
      <k-bar>
        <template #left>
          <k-headline> The JSON response could not be parsed </k-headline>
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
  props: {
    html: String
  },
  mounted() {
    try {
      let doc = this.$refs.iframe.contentWindow.document;
      doc.open();
      doc.write(this.html);
      doc.close();
    } catch (e) {
      console.error(e);
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
  z-index: var(--z-fatal);
  align-items: center;
  justify-content: center;
  padding: 1.5rem;
}
.k-fatal-box {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  color: var(--color-black);
  background: var(--color-red-400);
  box-shadow: var(--shadow-xl);
  border-radius: var(--rounded);
}
.k-fatal-box .k-headline {
  line-height: 1;
  font-size: var(--text-sm);
  padding: 0.75rem;
}
.k-fatal-box .k-button {
  padding: 0.75rem;
}
.k-fatal-iframe {
  border: 0;
  width: 100%;
  flex-grow: 1;
  background: var(--color-white);
}
</style>
