<template>
  <section class="k-info-section">
    <k-headline class="k-info-section-headline">{{ headline }}</k-headline>
    <k-box :theme="theme">
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
      issue: null,
      text: null,
      theme: null
    };
  },
  created() {
    this.load()
      .then(response => {
        this.headline = response.options.headline;
        this.text     = response.options.text;
        this.theme    = response.options.theme || "info";
      })
      .catch (error => {
        this.issue = error;
      });
  }
};
</script>

<style lang="scss">
.k-info-section-headline {
  margin-bottom: .5rem;
}
</style>
