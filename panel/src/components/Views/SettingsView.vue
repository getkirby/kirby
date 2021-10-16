<template>
  <k-inside>
    <k-view class="k-settings-view">
      <k-header>
        {{ $t("view.settings") }}
      </k-header>

      <section class="k-system-info">
        <header class="k-settings-view-section-header">
          <k-headline>Kirby</k-headline>
        </header>

        <ul class="k-system-info-box">
          <li>
            <dl>
              <dt>{{ $t("license") }}</dt>
              <dd>
                <template v-if="$license">
                  {{ license }}
                </template>
                <button
                  v-else
                  class="k-system-unregistered"
                  @click="$dialog('registration')"
                >
                  {{ $t("license.unregistered") }}
                </button>
              </dd>
            </dl>
          </li>
          <li>
            <dl>
              <dt>{{ $t("version") }}</dt>
              <dd dir="ltr">
                {{ version }}
              </dd>
            </dl>
          </li>
        </ul>
      </section>

      <section v-if="$multilang" class="k-languages">
        <template v-if="languages.length > 0">
          <section class="k-languages-section">
            <header class="k-settings-view-section-header">
              <k-headline>{{ $t("languages.default") }}</k-headline>
            </header>
            <k-collection :items="primaryLanguage" />
          </section>

          <section class="k-languages-section">
            <header class="k-settings-view-section-header">
              <k-headline>{{ $t("languages.secondary") }}</k-headline>
              <k-button
                :text="$t('language.create')"
                icon="add"
                @click="$dialog('languages/create')"
              />
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
          <header class="k-settings-view-section-header">
            <k-headline>{{ $t("languages") }}</k-headline>
            <k-button
              :text="$t('language.create')"
              icon="add"
              @click="$dialog('languages/create')"
            />
          </header>
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
    },
    license: String,
    version: String
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
.k-settings-view section {
  margin-bottom: 3rem;
}
.k-settings-view .k-header {
  margin-bottom: 1.5rem;
}
.k-settings-view-section-header {
  margin-bottom: 0.5rem;
  display: flex;
  justify-content: space-between;
}

.k-system-info-box {
  background: var(--color-white);
  padding: 0.75rem;
  display: flex;
}
.k-system-info-box li {
  flex-shrink: 0;
  flex-grow: 1;
  flex-basis: 0;
}

@media screen and (max-width: 40em) {
  .k-system-info-box {
    flex-direction: column;
  }

  .k-system-info-box li:not(:last-child) {
    margin-bottom: 0.5rem;
  }
}

.k-system-info-box dt {
  font-size: var(--text-sm);
  color: var(--color-gray-600);
  margin-bottom: 0.25rem;
}
.k-system-unregistered {
  color: var(--color-negative);
  font-weight: var(--font-bold);
}

.k-languages-section {
  margin-bottom: 2rem;
}
</style>
