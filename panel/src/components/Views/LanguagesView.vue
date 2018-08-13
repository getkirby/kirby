<template>
  <k-error-view v-if="issue">
    {{ issue.message }}
  </k-error-view>
  <k-view v-else class="k-languages-view">
    <k-header>
      {{ $t('view.languages') }}
      <k-button-group slot="left">
        <k-button :disabled="$permissions.languages.create === false" icon="add" @click="$refs.create.open()">{{ $t('language.create') }}</k-button>
      </k-button-group>
    </k-header>

    <template v-if="languages.length > 0">
      <section class="k-languages-section">
        <k-headline>{{ $t('languages.default') }}</k-headline>
        <k-collection :items="defaultLanguage" @action="action" />
      </section>

      <section class="k-languages-section">
        <k-headline>{{ $t('languages.secondary') }}</k-headline>
        <k-collection v-if="translations.length" :items="translations" @action="action" />
        <k-empty v-else icon="globe" @click="$refs.create.open()">{{ $t('languages.empty.secondary') }}</k-empty>
      </section>
    </template>
    <template v-else-if="languages.length === 0">
      <k-empty icon="globe" @click="$refs.create.open()">{{ $t('languages.empty') }}</k-empty>
    </template>

    <k-language-create-dialog ref="create" @success="fetch" />
    <k-language-update-dialog ref="update" @success="fetch" />
    <k-language-remove-dialog ref="remove" @success="fetch" />

  </k-view>

</template>

<script>
export default {
  data() {
    return {
      languages: [],
      issue: null
    };
  },
  created() {
    this.fetch();
  },
  computed: {
    defaultLanguage() {
      return this.languages.filter(language => language.default);
    },
    translations() {
      return this.languages.filter(language => language.default === false);
    }
  },
  methods: {
    fetch() {
      this.$store.dispatch("title", this.$t("view.languages"));

      this.$api
        .get("languages")
        .then(response => {
          this.languages = response.data.map(language => {
            return {
              id: language.code,
              default: language.default,
              icon: { type: "globe", back: "black" },
              text: language.name,
              info: language.code,
              options: [
                {
                  icon: "edit",
                  text: this.$t("edit"),
                  click: "update"
                },
                {
                  icon: "globe",
                  text: this.$t("language.makeDefault"),
                  disabled: language.default,
                  click: "primary"
                },
                {
                  icon: "trash",
                  text: this.$t("delete"),
                  disabled: language.default && response.data.length !== 1,
                  click: "remove"
                }
              ]
            };
          });
        })
        .catch(error => {
          this.issue = error;
        });

    },
    action(language, action) {
      switch (action) {
        case "update":
          this.$refs.update.open(language.id);
          break;
        case "primary":
          this.$api
            .patch("languages/" + language.id, {default: true})
            .then(() => {
              this.fetch();
              this.$store.dispatch("languages/load");
            });
          break;
        case "remove":
          this.$refs.remove.open(language.id);
          break;
      }
    }
  }
};
</script>

<style lang="scss">
.k-languages-section {
  margin-bottom: 2rem;
}
.k-languages-section .k-headline {
  margin-bottom: .5rem;
}
</style>
