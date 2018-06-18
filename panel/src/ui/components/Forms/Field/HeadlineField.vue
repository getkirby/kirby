<template>
  <kirby-headline class="kirby-headline-field">
    {{ label }}
  </kirby-headline>
</template>

<script>
export default {
  props: {
    label: String
  }
};
</script>

<style lang="scss">
.kirby-headline-field {
  position: relative;
  margin-bottom: -0.5rem;
  padding-top: 3rem;
  padding-left: 1.5em;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid $color-border;
  counter-increment: count;
}

.kirby-headline-field.kirby-headline > span {
  padding: 0;
}

.kirby-headline-field::before {
  position: absolute;
  content: counter(count, decimal-leading-zero);
  padding-bottom: 0.5rem;
  left: 0;
  color: $color-focus;
  font-weight: 400;
}
</style>
