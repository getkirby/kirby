<template>
  <div class="kirby-field kirby-info-field">
    <kirby-headline>{{ label }}</kirby-headline>
    <kirby-box :theme="theme" v-html="text" />
  </div>
</template>

<script>
export default {
  props: {
    label: String,
    text: String,
    theme: {
      type: String,
      default: "info"
    }
  }
};
</script>

<style lang="scss">

.kirby-info-field .kirby-headline {
  padding-bottom: .75rem;
  line-height: 1.25rem;
}

</style>
