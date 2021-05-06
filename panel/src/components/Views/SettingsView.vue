<template>
  <k-view class="k-settings-view">
    <k-header>
      {{ $t('view.settings') }}
    </k-header>

    <section class="k-system-info">
      <header>
        <k-headline>Kirby</k-headline>
      </header>

      <ul class="k-system-info-box">
        <li>
          <dl>
            <dt>{{ $t('license') }}</dt>
            <dd>
              <template v-if="license">
                {{ license }}
              </template>
              <p v-else>
                <strong class="k-system-unregistered">{{ $t('license.unregistered') }}</strong>
              </p>
            </dd>
          </dl>
        </li>
        <li>
          <dl>
            <dt>{{ $t('version') }}</dt>
            <dd>{{ $store.state.system.info.version }}</dd>
          </dl>
        </li>
      </ul>
    </section>

    <section v-if="multilang" class="k-languages">
      <template v-if="languages.length > 0">
        <section class="k-languages-section">
          <header>
            <k-headline>{{ $t('languages.default') }}</k-headline>
          </header>
          <k-collection :items="defaultLanguage" @action="action" />
        </section>

        <section class="k-languages-section">
          <header>
            <k-headline>{{ $t('languages.secondary') }}</k-headline>
            <k-button icon="add" @click="$refs.create.open()">
              {{ $t('language.create') }}
            </k-button>
          </header>
          <k-collection v-if="translations.length" :items="translations" @action="action" />
          <k-empty v-else icon="globe" @click="$refs.create.open()">
            {{ $t('languages.secondary.empty') }}
          </k-empty>
        </section>
      </template>

      <template v-else-if="languages.length === 0">
        <header>
          <k-headline>{{ $t('languages') }}</k-headline>
          <k-button icon="add" @click="$refs.create.open()">
            {{ $t('language.create') }}
          </k-button>
        </header>
        <k-empty icon="globe" @click="$refs.create.open()">
          {{ $t('languages.empty') }}
        </k-empty>
      </template>

      <k-language-create-dialog ref="create" @success="fetch" />
      <k-language-update-dialog ref="update" @success="fetch" />
      <k-language-remove-dialog ref="remove" @success="fetch" />
    </section>
  </k-view>
</template>

<script>
export default {
  data() {
    return {
      languages: [],
    };
  },
  computed: {
    defaultLanguage() {
      return this.languages.filter(language => language.default);
    },
    multilang() {
      return this.$store.state.system.info.multilang;
    },
    license() {
      return this.$store.state.system.info.license;
    },
    translations() {
      return this.languages.filter(language => language.default === false);
    }
  },
  created() {
    this.$store.dispatch("content/current", null);
    this.$store.dispatch("title", this.$t("view.settings"));
    this.$store.dispatch("breadcrumb", []);
    this.fetch();
  },
  methods: {
    fetch() {

      if (this.multilang !== true) {
        this.languages = [];
        return;
      }

      this.$api
        .get("languages")
        .then(response => {
          this.languages = response.data.map(language => {
            return {
              id: language.code,
              default: language.default,
              icon: { type: "globe", back: "black" },
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
                  disabled: language.default && response.data.length !== 1,
                  click: "remove"
                }
              ]
            };
          });
        });

    },
    action(language, action) {
      switch (action) {
        case "update":
          this.$refs.update.open(language.id);
          break;
        case "remove":
          this.$refs.remove.open(language.id);
          break;
      }
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
.k-settings-view header {
  margin-bottom: .5rem;
  display: flex;
  justify-content: space-between;
}

.k-system-info-box {
  background: var(--color-white);
  padding: .75rem;
  display: flex;
}
.k-system-info-box li {
  flex-shrink: 0;
  flex-grow: 1;
  flex-basis: 0;
}
.k-system-info-box dt {
  font-size: var(--text-sm);
  color: var(--color-gray-600);
  margin-bottom: .25rem;
}
.k-system-unregistered {
  color: var(--color-negative);
}

.k-languages-section {
  margin-bottom: 2rem;
}
</style>
