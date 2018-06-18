<template>
  <div :data-gutter="gutter" class="kirby-grid">
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
.kirby-grid {
  --columns: 12;
  display: grid;
  grid-template-columns: 1fr;
}
.kirby-grid[data-gutter="small"] {
  grid-gap: 1rem;
}
.kirby-grid[data-gutter="medium"] {
  grid-gap: 1.5rem;
}
.kirby-grid[data-gutter="large"] {
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
.kirby-grid[data-gutter="huge"] {
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
.kirby-grid[data-gutter="fluid"] {
  grid-column-gap: 5vw;
  grid-row-gap: 1.5rem;
}

@media screen and (min-width: $breakpoint-medium) {
  .kirby-grid {
    grid-template-columns: repeat(var(--columns), 1fr);
  }
}
</style>
