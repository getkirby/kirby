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
  grid-template-columns: repeat(auto-fit, minmax(12rem, 1fr));
}

@media screen and (min-width: $breakpoint-small) {
  .k-cards[data-size="tiny"] {
    grid-template-columns: repeat(auto-fill, minmax(12rem, 1fr));
  }
  .k-cards[data-size="small"] {
    grid-template-columns: repeat(auto-fill, minmax(16rem, 1fr));
  }
  .k-cards[data-size="medium"] {
    grid-template-columns: repeat(auto-fill, minmax(24rem, 1fr));
  }
  .k-cards[data-size="large"],
  .k-cards[data-size="huge"] {
    grid-template-columns: 1fr;
  }
}

@media screen and (min-width: $breakpoint-medium) {
  .k-cards[data-size="large"] {
    grid-template-columns: repeat(auto-fill, minmax(32rem, 1fr));
  }
}

</style>
