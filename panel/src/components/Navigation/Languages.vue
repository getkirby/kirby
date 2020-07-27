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
      return this.$languages.find(language => language.default === true);
    },
    language() {
      return this.$language;
    },
    languages() {
      return this.$languages.filter(language => language.default === false);
    }
  },
  methods: {
    change(language) {
      this.$emit("change", language);
      this.$go(this.$view.path, {
        data: {
          language: language.code
        }
      });
    }
  }
}
</script>
