<template>
  <div :data-width="width" :data-sticky="sticky" class="k-column">
    <div><slot /></div>
  </div>
</template>

<script>
export default {
  props: {
    width: String,
    sticky: Boolean
  }
};
</script>

<style lang="scss">
.k-column {
  min-width: 0;
  grid-column-start: span 12;
}

.k-column[data-sticky] > div {
  position: sticky;
  top: 4vh;
  z-index: 2;
}

@media screen and (min-width: $breakpoint-md) {
  .k-column[data-width="1/1"],
  .k-column[data-width="2/2"],
  .k-column[data-width="3/3"],
  .k-column[data-width="4/4"],
  .k-column[data-width="6/6"],
  .k-column[data-width="12/12"] {
    grid-column-start: span 12;
  }
  .k-column[data-width="11/12"] {
    grid-column-start: span 11;
  }
  .k-column[data-width="5/6"],
  .k-column[data-width="10/12"] {
    grid-column-start: span 10;
  }
  .k-column[data-width="3/4"],
  .k-column[data-width="9/12"] {
    grid-column-start: span 9;
  }
  .k-column[data-width="2/3"],
  .k-column[data-width="4/6"],
  .k-column[data-width="8/12"] {
    grid-column-start: span 8;
  }
  .k-column[data-width="7/12"] {
    grid-column-start: span 7;
  }
  .k-column[data-width="1/2"],
  .k-column[data-width="2/4"],
  .k-column[data-width="3/6"],
  .k-column[data-width="6/12"] {
    grid-column-start: span 6;
  }
  .k-column[data-width="5/12"] {
    grid-column-start: span 5;
  }
  .k-column[data-width="1/3"],
  .k-column[data-width="2/6"],
  .k-column[data-width="4/12"] {
    grid-column-start: span 4;
  }
  .k-column[data-width="1/4"],
  .k-column[data-width="3/12"] {
    grid-column-start: span 3;
  }
  .k-column[data-width="1/6"],
  .k-column[data-width="2/12"] {
    grid-column-start: span 2;
  }
  .k-column[data-width="1/12"] {
    grid-column-start: span 1;
  }
}

.k-column[data-disabled] {
  cursor: not-allowed;
  opacity: .4;
}
.k-column[data-disabled] * {
  pointer-events: none;
}
.k-column[data-disabled] .k-text[data-theme=help] * {
  pointer-events: initial;
}
</style>
