<template>
  <div :data-gutter="gutter" class="k-grid">
    <slot />
  </div>
</template>

<script>
export default {
  props: {
    gutter: String,
  }
};
</script>

<style lang="scss">
.k-grid {
  --columns: 12;
  display: grid;
  grid-template-columns: 1fr;
}
.k-grid[data-gutter="small"] {
  grid-gap: 1rem;
}
.k-grid[data-gutter="medium"] {
  grid-gap: 1.5rem;
}
.k-grid[data-gutter="large"] {
  grid-gap: 1.5rem;
  @media screen and (min-width: $breakpoint-medium) {
    grid-gap: 3rem;
  }
  @media screen and (min-width: $breakpoint-large) {
    grid-gap: 4.5rem;
  }
  @media screen and (min-width: $breakpoint-huge) {
    grid-gap: 6rem;
  }
}
.k-grid[data-gutter="huge"] {
  grid-gap: 1.5rem;
  @media screen and (min-width: $breakpoint-medium) {
    grid-gap: 4.5rem;
  }
  @media screen and (min-width: $breakpoint-large) {
    grid-gap: 6rem;
  }
  @media screen and (min-width: $breakpoint-huge) {
    grid-gap: 7.5rem;
  }
}
.k-grid[data-gutter="fluid"] {
  grid-column-gap: 5vw;
  grid-row-gap: 1.5rem;
}

@media screen and (min-width: $breakpoint-medium) {
  .k-grid {
    grid-template-columns: repeat(var(--columns), 1fr);
  }
}
</style>
