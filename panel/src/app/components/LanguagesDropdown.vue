<template>
  <k-select-dropdown
    :options="languages"
    icon="globe"
    @change="onChange"
  />
</template>

<script>
export default {
  computed: {
    languages() {
      return this.$store.state.languages.all.map(language => {
        return {
          ...language,
          text: language.code,
          current: language.code === this.$store.state.languages.current
        }
      });
    }
  },
  methods: {
    onChange(language) {
      this.$store.dispatch("languages/current", language)
    }
  }
}
</script>
