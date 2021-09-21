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
import SectionMixin from "@/mixins/section/section.js";
export default {
  mixins: [SectionMixin],
  data() {
    return {
      headline: null,
      text: null,
      theme: null
    };
  },
  created() {
    this.load()
      .then(response => {
        this.headline = response.headline;
        this.text = response.text;
        this.theme = response.theme || "info";
      });
  }
};
</script>

<style>

.k-info-section-headline {
  margin-bottom: .5rem;
}

</style>
