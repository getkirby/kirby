<template>
  <div class="k-headline-field">
    <k-headline :data-numbered="numbered" size="large">
      {{ label }}
    </k-headline>
    <footer v-if="help" class="k-field-footer">
      <!-- eslint-disable-next-line vue/no-v-html -->
      <k-text
        v-if="help"
        theme="help"
        class="k-field-help"
        v-html="help"
      />
    </footer>
  </div>
</template>

<script>
export default {
  props: {
    help: String,
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

  // don't add the top padding,
  // if the headline is the very first form element
  .k-fieldset > .k-grid .k-column:first-child & {
    padding-top: 0;
  }
}

.k-headline-field .k-headline[data-numbered]::before {
  counter-increment: headline-counter;
  content: counter(headline-counter, decimal-leading-zero);
  color: $color-focus;
  font-weight: 400;
  padding-right: .25rem;
}

</style>
