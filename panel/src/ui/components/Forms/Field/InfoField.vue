<template>
  <div class="k-field k-info-field">
    <k-headline>{{ label }}</k-headline>
    <k-box :theme="theme">
      <k-text v-html="text" />
    </k-box>
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

.k-info-field .k-headline {
  padding-bottom: .75rem;
  line-height: 1.25rem;
}

</style>
