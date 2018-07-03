<template>
  <div :data-width="width" class="kirby-column">
    <div class="kirby-column-content">
      <slot />
    </div>
  </div>
</template>

<script>
export default {
  name: "KirbyColumn",
  props: {
    width: String
  }
};
</script>

<style lang="scss">
.kirby-column {
  min-width: 0;
  grid-column-start: span 12;
}

@media screen and (min-width: $breakpoint-medium) {
  .kirby-column[data-width="1/1"],
  .kirby-column[data-width="2/2"],
  .kirby-column[data-width="3/3"],
  .kirby-column[data-width="4/4"],
  .kirby-column[data-width="6/6"] {
    grid-column-start: span 12;
  }
  .kirby-column[data-width="1/2"],
  .kirby-column[data-width="2/4"],
  .kirby-column[data-width="3/6"] {
    grid-column-start: span 6;
  }
  .kirby-column[data-width="1/3"],
  .kirby-column[data-width="2/6"] {
    grid-column-start: span 4;
  }
  .kirby-column[data-width="2/3"],
  .kirby-column[data-width="4/6"] {
    grid-column-start: span 8;
  }
  .kirby-column[data-width="1/4"] {
    grid-column-start: span 3;
  }
  .kirby-column[data-width="1/6"] {
    grid-column-start: span 2;
  }
  .kirby-column[data-width="5/6"] {
    grid-column-start: span 10;
  }
  .kirby-column[data-width="3/4"] {
    grid-column-start: span 9;
  }
}
</style>
