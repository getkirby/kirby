<template>
  <k-dropdown v-if="languages.length && languages.length > 1">
    <k-button :responsive="true" icon="globe" @click="$refs.languages.toggle()">
      {{ language.name }}
    </k-button>
    <k-dropdown-content v-if="languages" ref="languages">
      <k-dropdown-item v-for="language in languages" @click="change(language)">{{ language.name }}</k-dropdown-item>
    </k-dropdown-content>
  </k-dropdown>
</template>

<script>
export default {
  computed: {
    language() {
      return this.$store.state.languages.current;
    },
    languages() {
      return this.$store.state.languages.all;
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
