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
    current() {
      return this.$store.state.languages.current
    },
    languages() {
      return this.$store.state.languages.all.map(language => {
        return {
          ...language,
          text: language.code,
          current: language.code === this.current.code
        }
      });
    }
  },
  methods: {
    onChange(language) {
      this.$store.dispatch("languages/current", language);
      this.$emit("change", language);
    }
  }
}
</script>
