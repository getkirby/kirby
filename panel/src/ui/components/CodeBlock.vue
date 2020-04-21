<template>
  <pre class="k-code-block"><code v-html="code || ' '" /></pre>
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
  background: $color-black;
  padding: .75rem;
  overflow-x: auto;
}
.k-code-block code {
  color: $color-white;
  font-family: $font-mono;
  font-size: .75rem;
  line-height: 1.5em;
  border-radius: $rounded-sm;
}
</style>
