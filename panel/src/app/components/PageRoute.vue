<template>
  <k-page-view
    :page="page"
    @remove="onRemoved"
    @update="load"
  />
</template>
<script>
export default {
  props: {
    id: {
      type: String
    }
  },
  data() {
    return {
      page: {}
    };
  },
  created() {
    this.load();
  },
  watch: {
    "$route": "load",
  },
  methods: {
    async load() {
      this.page = await this.$model.page.get(this.id);
    },
    onSlug(page) {
      // Redirect, if slug was changed in default language
      if (
        !this.$store.state.languages.current ||
        this.$store.state.languages.current.default === true
      ) {
        const path = this.$model.pages.link(page.id);
        this.$router.push(path);
      }
    },
    onRemoved() {
      if (this.page.parent) {
        const path = this.$model.pages.link(this.page.parent.id);
        this.$router.push(path);
      } else {
        this.$router.push("/pages");
      }
    },
  }
}
</script>
