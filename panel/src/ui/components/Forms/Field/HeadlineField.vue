<template>
  <kirby-headline :data-numbered="numbered" size="large" class="kirby-headline-field">
    {{ label }}
  </kirby-headline>
</template>

<script>
export default {
  props: {
    label: String,
    numbered: Boolean
  }
};
</script>

<style lang="scss">
body {
  counter-reset: headline-counter;
}
.kirby-headline-field {
  position: relative;
  padding-top: 1.5rem;
}

.kirby-headline-field[data-numbered]::before {
  counter-increment: headline-counter;
  content: counter(headline-counter, decimal-leading-zero);
  color: $color-focus;
  font-weight: 400;
  padding-right: .25rem;
}

/* don't add the top padding, if the headline is the very first form element */
.kirby-fieldset > .kirby-grid .kirby-column:first-child .kirby-headline-field {
  padding-top: 0;
}
</style>
