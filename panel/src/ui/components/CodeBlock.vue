<template>
  <pre class="k-code-block p-3 bg-black rounded-sm"><code class="text-white font-mono text-xs" v-html="code || ' '" /></pre>
</template>

<script>
export default {
  props: {
    code: {
      type: [String, Boolean, Array, Object, Number],
    }
  },
};
</script>

<style lang="scss">
.k-code-block {
  overflow-x: auto;
}
.k-code-block code {
  line-height: 1.5em;
}
</style>
