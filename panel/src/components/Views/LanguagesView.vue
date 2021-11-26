<template>
  <k-inside>
    <k-view class="k-languages-view">
      <k-header>
        {{ $t("view.languages") }}

        <k-button-group slot="left">
          <k-button
            :text="$t('language.create')"
            icon="add"
            @click="$dialog('languages/create')"
          />
        </k-button-group>
      </k-header>

      <section class="k-languages">
        <template v-if="languages.length > 0">
          <section class="k-languages-view-section">
            <header class="k-languages-view-section-header">
              <k-headline>{{ $t("languages.default") }}</k-headline>
            </header>
            <k-collection :items="primaryLanguage" />
          </section>

          <section class="k-languages-view-section">
            <header class="k-languages-view-section-header">
              <k-headline>{{ $t("languages.secondary") }}</k-headline>
            </header>
            <k-collection
              v-if="secondaryLanguages.length"
              :items="secondaryLanguages"
            />
            <k-empty v-else icon="globe" @click="$dialog('languages/create')">
              {{ $t("languages.secondary.empty") }}
            </k-empty>
          </section>
        </template>

        <template v-else-if="languages.length === 0">
          <k-empty icon="globe" @click="$dialog('languages/create')">
            {{ $t("languages.empty") }}
          </k-empty>
        </template>
      </section>
    </k-view>
  </k-inside>
</template>

<script>
export default {
  props: {
    languages: {
      type: Array,
      default() {
        return [];
      }
    }
  },
  computed: {
    languagesCollection() {
      return this.languages.map((language) => ({
        ...language,
        image: {
          back: "black",
          color: "gray",
          icon: "globe"
        },
        link: () => {
          this.$dialog(`languages/${language.id}/update`);
        },
        options: [
          {
            icon: "edit",
            text: this.$t("edit"),
            click() {
              this.$dialog(`languages/${language.id}/update`);
            }
          },
          {
            icon: "trash",
            text: this.$t("delete"),
            disabled: language.default && this.languages.length !== 1,
            click() {
              this.$dialog(`languages/${language.id}/delete`);
            }
          }
        ]
      }));
    },
    primaryLanguage() {
      return this.languagesCollection.filter((language) => language.default);
    },
    secondaryLanguages() {
      return this.languagesCollection.filter(
        (language) => language.default === false
      );
    }
  }
};
</script>

<style>
.k-languages-view .k-header {
  margin-bottom: 1.5rem;
}
.k-languages-view-section-header {
  margin-bottom: 0.5rem;
}
.k-languages-view-section {
  margin-bottom: 3rem;
}
</style>
