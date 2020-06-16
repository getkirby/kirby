<template>
  <component :is="element" class="k-auto-grid">
    <slot />
  </component>
</template>

<script>
export default {
  props: {
    element: {
      type: String,
      default: "div"
    }
  }
};
</script>

<style lang="scss">
.k-auto-grid {
  --gap: 0;
  --max: 1fr;
  --min: 12rem;
  --col-gap: var(--gap);
  --row-gap: var(--gap);

  display: grid;
  grid-column-gap: var(--col-gap);
  grid-row-gap: var(--row-gap);
  grid-template-columns: repeat(auto-fit, minmax(var(--min), var(--max)));
  /**
    Making sure card doesn't break layout if
    in a parent narrower than 12rem

    TODO: refactor once min() is supported by all our browsers
    since LibSASS has its issues with min() we need to be tricky
    */
  grid-template-columns: repeat(auto-fit, minmax(#{"min(var(--min), 100%)"}, var(--max)));
}
</style>
