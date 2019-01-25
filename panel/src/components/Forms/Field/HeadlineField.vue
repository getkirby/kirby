<template>
  <k-headline :data-numbered="numbered" size="large" class="k-headline-field">
    {{ label }}
  </k-headline>
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
.k-headline-field {
  position: relative;
  padding-top: 1.5rem;
}

.k-headline-field[data-numbered]::before {
  counter-increment: headline-counter;
  content: counter(headline-counter, decimal-leading-zero);
  color: $color-focus;
  font-weight: 400;
  padding-right: .25rem;
}

/* don't add the top padding, if the headline is the very first form element */
.k-fieldset > .k-grid .k-column:first-child .k-headline-field {
  padding-top: 0;
}
</style>
