<template>
  <section class="k-info-section">
    <k-headline class="k-info-section-headline">{{ headline }}</k-headline>
    <k-box :theme="theme">
      <k-text v-html="text" />
    </k-box>
  </section>
</template>

<script>
import SectionMixin from "@/mixins/section.js";

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
  created: function() {
    this.fetch();
  },
  methods: {
    fetch() {
      this.$api
        .get(this.parent + "/sections/" + this.name)
        .then(response => {
          this.headline = response.data.headline;
          this.text     = response.data.text;
          this.theme    = response.data.theme || "info";
        })
        .catch(error => {
          this.issue = error;
        });
    }
  }
};
</script>
<style>

.k-info-section-headline {
  margin-bottom: .5rem;
}

</style>

