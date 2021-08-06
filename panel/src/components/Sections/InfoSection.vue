<template>
  <section class="k-info-section">
    <k-headline class="k-info-section-headline">
      {{ headline }}
    </k-headline>
    <k-box :theme="theme">
      <!-- eslint-disable-next-line vue/no-v-html -->
      <k-text v-html="text" />
    </k-box>
  </section>
</template>

<script>
export default {
  props: {
    headline: String,
    text: String,
    theme: {
      type: String,
      default: "info"
    }
  }
};
</script>
<style>

.k-info-section-headline {
  margin-bottom: .5rem;
}

</style>
