<template>
  <section class="kirby-info-section">
    <kirby-headline class="kirby-info-section-headline">{{ headline }}</kirby-headline>
    <kirby-box v-html="text" :theme="theme" />
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
      text: null
    };
  },
  created: function() {
    this.fetch();
  },
  methods: {
    fetch() {
      this.$api
        .get(this.parent + "/" + this.name)
        .then(response => {
          this.headline = response.data.headline;
          this.text     = response.data.text;
          this.theme    = response.data.theme;
        })
        .catch(error => {
          this.issue = error;
        });
    }
  }
};
</script>
<style>

.kirby-info-section-headline {
  margin-bottom: .5rem;
}

</style>

