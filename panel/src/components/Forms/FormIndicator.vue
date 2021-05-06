<template>
  <k-dropdown v-if="hasChanges" class="k-form-indicator">
    <k-button class="k-topbar-button" @click="toggle">
      <k-icon type="edit" class="k-form-indicator-icon" />
    </k-button>

    <k-dropdown-content ref="list" align="right">
      <p class="k-form-indicator-info">
        {{ $t("lock.unsaved") }}:
      </p>
      <hr>
      <k-dropdown-item
        v-for="entry in entries"
        :key="entry.id"
        :icon="entry.icon"
        @click.native.stop="go(entry.target)"
      >
        {{ entry.label }}
      </k-dropdown-item>
    </k-dropdown-content>
  </k-dropdown>
</template>

<script>
export default {
  data() {
    return {
      isOpen: false,
      entries: []
    }
  },
  computed: {
    store() {
      return this.$store.state.content.models;
    },
    models() {
      const ids = Object.keys(this.store).filter(id => {
        return this.store[id] ? true : false;
      });

      let models = ids.map(id => {
        return {
          id: id,
          ...this.store[id]
        };
      });
      return models.filter(model => Object.keys(model.changes).length > 0);
    },
    hasChanges() {
      return this.models.length > 0;
    }
  },
  methods: {
    go(target) {
      // if a target language is set and it is not the current language,
      // switch to it before routing to target view
      if (target.language) {
        if (this.$store.state.languages.current.code !== target.language) {
          const language = this.$store.state.languages.all.filter(l => l.code === target.language)[0];
          this.$store.dispatch("languages/current", language);
        }
      }

      this.$go(target.link);
    },
    load() {
      // create an API request promise for each model with changes
      const promises = this.models.map(model => {
        return this.$api.get(model.api, { view: "compact" }, null, true).then(response => {

          // populate entry depending on model type
          let entry;

          if (model.id.startsWith("pages/") === true) {
            entry = {
              icon: "page",
              label: response.title,
              target: {
                link: this.$api.pages.link(response.id)
              }
            };

          } else if (model.id.startsWith("files/") === true) {
            entry = {
              icon: "image",
              label: response.filename,
              target: {
                link: response.link
              }
            };

          } else if (model.id.startsWith("users/") === true) {
            entry = {
              icon: "user",
              label: response.email,
              target: {
                link: this.$api.users.link(response.id),
              }
            };
          } else {
            entry = {
              icon: "home",
              label: response.title,
              target: {
                link: "/site"
              }
            };
          }

          // add language indicator if in multilang
          if (this.$store.state.languages.current) {
            const language = model.id.split("/").pop();
            entry.label = entry.label + " (" + language + ")";
            entry.target.language = language;
          }

          return entry;
        }).catch(() => {
          this.$store.dispatch("content/remove", model.id);
          return null;
        });
      });

      return Promise.all(promises).then(entries => {
        this.entries = entries.filter(entry => {
          return entry !== null;
        });

        if (this.entries.length === 0) {
          this.$store.dispatch("notification/success", this.$t("lock.unsaved.empty"));
        }
      });
    },
    toggle() {
      if (this.$refs.list.isOpen === false) {
        this.load().then(() => {
          if (this.$refs.list) {
            this.$refs.list.toggle();
          }
        });
      } else {
        this.$refs.list.toggle();
      }
    }
  }
};
</script>

<style>
.k-form-indicator-icon {
  color: var(--color-notice-light);
}
.k-form-indicator-info {
  font-size: var(--text-sm);
  font-weight: var(--font-bold);
  padding: .75rem 1rem .25rem;
  line-height: 1.25em;
  width: 15rem;
}
</style>
