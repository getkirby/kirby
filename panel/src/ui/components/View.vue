<template>
  <div
    :data-align="align"
    class="k-view px-6"
  >
    <!-- @slot View content -->
    <slot />
  </div>
</template>

<script>
export default {
  props: {
    /**
     * Available values: `center`
     */
    align: String
  }
};
</script>

<style lang="scss">
.k-view {
  margin: 0 auto;
  max-width: 100rem;

  @media screen and (min-width: $breakpoint-sm) {
    padding-left: 3rem;
    padding-right: 3rem;
  }

  @media screen and (min-width: $breakpoint-lg) {
    padding-left: 6rem;
    padding-right: 6rem;
  }
}

.k-view[data-align="center"] {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.5rem;
  overflow: auto;
}
.k-view[data-align="center"] > * {
  flex-basis: 22.5rem;
}
</style>
