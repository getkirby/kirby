<template>
  <div class="k-languages">
    <template v-if="languages.length > 0">
      <k-section
        :label="$t('languages.default')"
        class="k-languages-section mb-12"
      >
        <k-collection
          :items="defaultLanguage"
          @option="onOption"
        />
      </k-section>

      <k-section
        :label="$t('languages.secondary')"
        :options="[
          { icon: 'add', text: $t('language.create') }
        ]"
        class="k-languages-section"
        @option="$refs.create.open()"
      >
        <k-collection
          v-if="translations.length"
          :items="translations"
          @option="onOption"
        />
        <k-empty
          v-else
          icon="globe"
          @click="$refs.create.open()"
        >
          {{ $t('languages.secondary.empty') }}
        </k-empty>
      </k-section>
    </template>

    <template v-else-if="languages.length === 0">
      <k-section
        :label="$t('languages')"
        :options="[
          { icon: 'add', text: $t('language.create') }
        ]"
        @option="$refs.create.open()"
      >
        <k-empty
          icon="globe"
          @click="$refs.create.open()"
        >
          {{ $t('languages.empty') }}
        </k-empty>
      </k-section>
    </template>

    <k-language-create-dialog ref="create" @success="load" />
    <k-language-remove-dialog ref="remove" @success="load" />
    <k-language-update-dialog ref="update" @success="load" />
  </div>
</template>
<script>
export default {
  data() {
    return {
      languages: []
    }
  },
  created() {
    this.load();
  },
  computed: {
    defaultLanguage() {
      return this.languages.filter(language => language.default);
    },
    translations() {
      return this.languages.filter(language => !language.default);
    }
  },
  methods: {
    async load() {
      this.languages = await this.$model.languages.list();
      this.languages = this.languages.map(language => {
        return {
          id: language.code,
          default: language.default,
          icon: {
            type: "globe"
          },
          image: true,
          text: language.name,
          info: language.code,
          link: () => {
            this.$refs.update.open(language.code);
          },
          options: [
            {
              icon: "edit",
              text: this.$t("edit"),
              click: "update"
            },
            {
              icon: "trash",
              text: this.$t("delete"),
              disabled: language.default && this.languages.length !== 1,
              click: "remove"
            }
          ]
        };

      });
    },
    onOption(option, language) {
      switch (option) {
        case "remove":
          return this.$refs.remove.open(language.id);
        case "update":
          return this.$refs.update.open(language.id);
      }
    }
  }
}
</script>
