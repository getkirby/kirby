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
      return this.$store.state.languages.current;
    },
    default() {
      return this.$store.state.languages.default;
    },
    languages() {
      let options    = [];
      const toOption = (language) => {
        return {
          ...language,
          text: language.name || language.code,
          current: language.code === this.current.code
        };
      };

      if (this.default) {
        options.push(toOption(this.default));
        options.push("-")
      }



      return [
        ...options,
        ...this.$store.state.languages.all.filter(language => language.code !== this.default.code).map(toOption)
      ];
    }
  },
  methods: {
    onChange(language) {
      this.$emit("change", language);
    }
  }
}
</script>
