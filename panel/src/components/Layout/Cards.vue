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
/**
 * The Cards component builds a very responsive grid of cards from the given cards option or by injecting cards into the default slot.
 */
export default {
  props: {
    cards: Array
  }
};
</script>

<style>
.k-cards {
  display: grid;
  grid-gap: 1.5rem;
  grid-template-columns: repeat(auto-fill, minmax(min(12rem, 100%), 1fr));
}

@media screen and (min-width: 30em) {
  .k-cards[data-size="tiny"] {
    grid-template-columns: repeat(auto-fill, minmax(min(10rem, 100%), 1fr));
  }
  .k-cards[data-size="small"] {
    grid-template-columns: repeat(auto-fill, minmax(min(16rem, 100%), 1fr));
  }
  .k-cards[data-size="medium"] {
    grid-template-columns: repeat(auto-fill, minmax(min(24rem, 100%), 1fr));
  }
  .k-cards[data-size="large"],
  .k-cards[data-size="huge"] {
    grid-template-columns: 1fr;
  }
}

@media screen and (min-width: 65em) {
  .k-cards[data-size="large"] {
    grid-template-columns: repeat(auto-fill, minmax(min(32rem, 100%), 1fr));
  }
}
</style>
