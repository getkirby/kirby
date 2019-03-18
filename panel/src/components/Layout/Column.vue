<template>
  <div :data-width="width" class="k-column">
    <slot />
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
.k-column {
  min-width: 0;
  grid-column-start: span 12;
}

@media screen and (min-width: $breakpoint-medium) {
  .k-column[data-width="1/1"],
  .k-column[data-width="2/2"],
  .k-column[data-width="3/3"],
  .k-column[data-width="4/4"],
  .k-column[data-width="6/6"] {
    grid-column-start: span 12;
  }
  .k-column[data-width="1/2"],
  .k-column[data-width="2/4"],
  .k-column[data-width="3/6"] {
    grid-column-start: span 6;
  }
  .k-column[data-width="1/3"],
  .k-column[data-width="2/6"] {
    grid-column-start: span 4;
  }
  .k-column[data-width="2/3"],
  .k-column[data-width="4/6"] {
    grid-column-start: span 8;
  }
  .k-column[data-width="1/4"] {
    grid-column-start: span 3;
  }
  .k-column[data-width="1/6"] {
    grid-column-start: span 2;
  }
  .k-column[data-width="5/6"] {
    grid-column-start: span 10;
  }
  .k-column[data-width="3/4"] {
    grid-column-start: span 9;
  }
}
</style>
