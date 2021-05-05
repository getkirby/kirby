<template>
  <div class="k-headline-field">
    <k-headline :data-numbered="numbered" size="large">
      {{ label }}
    </k-headline>
    <footer v-if="help" class="k-field-footer">
      <!-- eslint-disable vue/no-v-html -->
      <k-text
        v-if="help"
        theme="help"
        class="k-field-help"
        v-html="help"
      />
      <!-- eslint-enable vue/no-v-html -->
    </footer>
  </div>
</template>

<script>
/**
 * @example <k-headline-field label="This is a headline" />
 */
export default {
  props: {
    /**
     * Help text for below the field
     */
    help: String,
    /**
     * Label of the field
     */
    label: String,
    /**
     * Enables or hides the index numbers left next to the headline
     */
    numbered: Boolean
  }
};
</script>

<style>
body {
  counter-reset: headline-counter;
}
.k-headline-field {
  position: relative;
  padding-top: 1.5rem;

}
/* don't add the top padding,
if the headline is the very first form element */
.k-fieldset > .k-grid .k-column:first-child .k-headline-field {
  padding-top: 0;
}

.k-headline-field .k-headline[data-numbered]::before {
  counter-increment: headline-counter;
  content: counter(headline-counter, decimal-leading-zero);
  color: var(--color-focus);
  font-weight: 400;
  padding-right: .25rem;
}
</style>
