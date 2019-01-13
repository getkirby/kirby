<template>
  <div :data-align="align" class="k-view" >
    <slot />
  </div>
</template>

<script>
export default {
  props: {
    align: String
  }
};
</script>

<style lang="scss">
.k-view {
  padding-left: 1.5rem;
  padding-right: 1.5rem;
  margin: 0 auto;
  max-width: 100rem;

  @media screen and (min-width: $breakpoint-small) {
    padding-left: 3rem;
    padding-right: 3rem;
  }

  @media screen and (min-width: $breakpoint-large) {
    padding-left: 6rem;
    padding-right: 6rem;
  }
}

.k-view[data-align="center"] {
  height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 3rem;
  overflow: auto;
}
.k-view[data-align="center"] > * {
  flex-basis: 22.5rem;
}
</style>
