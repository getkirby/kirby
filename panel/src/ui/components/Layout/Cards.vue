<template>
  <div class="k-cards">
    <slot>
      <k-card
        v-for="(card, index) in cards"
        :key="index"
        v-bind="card"
        v-on="$listeners"
      />
    </slot>
  </div>
</template>

<script>
export default {
  props: {
    cards: Array
  }
}
</script>

<style lang="scss">
.k-cards {
  display: grid;
  grid-gap: 1.5rem;
  grid-template-columns: 1fr;
}

.k-sections > .k-column[data-width="3/4"] .k-cards,
.k-sections > .k-column[data-width="1/1"] .k-cards {
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
}

@media screen and (min-width: $breakpoint-medium) {
  .k-sections > .k-column[data-width="1/2"] .k-cards {
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  }
}

</style>
