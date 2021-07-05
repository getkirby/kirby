<template>
  <div
    :data-language="language.code"
    :dir="$translation.direction"
    class="k-panel k-panel-outside"
  >
    <slot />
  </div>
</template>

<script>
export default {
  computed: {
    language() {
      return this.$language ? this.$language.code : false;
    }
  }
}
</script>