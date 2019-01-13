<template>
  <k-dropdown v-if="languages.length">
    <k-button :responsive="true" icon="globe" @click="$refs.languages.toggle()">
      {{ language.name }}
    </k-button>
    <k-dropdown-content v-if="languages" ref="languages">
      <k-dropdown-item @click="change(defaultLanguage)">{{ defaultLanguage.name }}</k-dropdown-item>
      <hr>
      <k-dropdown-item v-for="language in languages" :key="language.code" @click="change(language)">
        {{ language.name }}
      </k-dropdown-item>
    </k-dropdown-content>
  </k-dropdown>
</template>

<script>
export default {
  computed: {
    defaultLanguage() {
      return this.$store.state.languages.default;
    },
    language() {
      return this.$store.state.languages.current;
    },
    languages() {
      return this.$store.state.languages.all.filter(language => language.default === false);
    }
  },
  methods: {
    change(language) {
      this.$store.dispatch("languages/current", language);
      this.$emit("change", language);
    }
  }
}
</script>
