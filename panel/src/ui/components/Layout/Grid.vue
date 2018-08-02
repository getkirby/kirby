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
  grid-column-gap: 0;
  grid-row-gap: 0;
  grid-template-columns: 1fr;
}

@media screen and (min-width: $breakpoint-small) {

  .k-grid[data-gutter="small"] {
    grid-column-gap: 1rem;
    grid-row-gap: 1rem;
  }

  .k-grid[data-gutter="medium"],
  .k-grid[data-gutter="large"],
  .k-grid[data-gutter="huge"] {
    grid-column-gap: 1.5rem;
    grid-row-gap: 1.5rem;
  }

}

@media screen and (min-width: $breakpoint-medium) {
  .k-grid {
    grid-template-columns: repeat(var(--columns), 1fr);
  }
  .k-grid[data-gutter="large"] {
    grid-column-gap: 3rem;
  }
  .k-grid[data-gutter="huge"] {
    grid-column-gap: 4.5rem;
  }
}

@media screen and (min-width: $breakpoint-large) {
  .k-grid[data-gutter="large"] {
    grid-column-gap: 4.5rem;
  }
  .k-grid[data-gutter="huge"] {
    grid-column-gap: 6rem;
  }

}

@media screen and (min-width: $breakpoint-huge) {
  .k-grid[data-gutter="large"] {
    grid-column-gap: 6rem;
  }
  .k-grid[data-gutter="huge"] {
    grid-column-gap: 7.5rem;
  }
}

</style>
