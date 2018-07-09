<template>
  <div class="kirby-cards">
    <slot>
      <kirby-card
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
.kirby-cards {
  display: grid;
  grid-gap: 1.5rem;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
}
.kirby-sections > .kirby-column[data-width="1/4"] .kirby-cards {
  grid-template-columns: repeat(auto-fit, minmax(175px, 1fr));
}
</style>
