<template>
  <k-dropdown v-if="languages.length" class="k-languages-dropdown">
    <k-button
      :text="language.name"
      :responsive="true"
      icon="globe"
      @click="$refs.languages.toggle()"
    />
    <k-dropdown-content v-if="languages" ref="languages">
      <k-dropdown-item @click="change(defaultLanguage)">
        {{ defaultLanguage.name }}
      </k-dropdown-item>
      <hr />
      <k-dropdown-item
        v-for="languageItem in languages"
        :key="languageItem.code"
        @click="change(languageItem)"
      >
        {{ languageItem.name }}
      </k-dropdown-item>
    </k-dropdown-content>
  </k-dropdown>
</template>

<script>
export default {
  computed: {
    defaultLanguage() {
      return this.$languages.find((language) => language.default === true);
    },
    language() {
      return this.$language;
    },
    languages() {
      return this.$languages.filter((language) => language.default === false);
    }
  },
  methods: {
    change(language) {
      this.$emit("change", language);
      this.$go(window.location, {
        query: {
          language: language.code
        }
      });
    }
  }
};
</script>
