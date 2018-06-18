<template>
  <div class="kirby-field kirby-info-field">
    <kirby-headline>{{ label }}</kirby-headline>
    <kirby-text v-html="text" />
  </div>
</template>

<script>
export default {
  props: {
    label: String,
    text: String
  }
};
</script>
