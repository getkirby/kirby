<template>
  <k-dropdown v-if="storage.length > 0" class="k-form-indicator">
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
        :key="entry.link"
        :icon="entry.icon"
        :link="entry.link"
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
      entries: [],
      storage: [],
    }
  },
  computed: {
    store() {
      return this.$store.state.form.models;
    }
  },
  watch: {
    store: {
      handler() {
        this.loadFromStorage();
      },
      deep: true
    }
  },
  created() {
    this.loadFromStorage();
  },
  methods: {
    loadFromApi() {
      let promises = this.storage.map(model => {
        return this.$api.get(model.api, { view: "compact" }, null, true).then(response => {
          if (model.id.startsWith("pages/")) {
            return {
              icon: "page",
              label: response.title,
              link: this.$api.pages.link(response.id),
            };
          }

          if (model.id.startsWith("files/")) {
            return {
              icon: "image",
              label: response.filename,
              link: response.link,
            };
          }

          if (model.id.startsWith("users/")) {
            return {
              icon: "user",
              label: response.email,
              link: this.$api.users.link(response.id),
            };
          }
        });
      });

      return Promise.all(promises).then(entries => {
        this.entries = entries;
      });
    },
    loadFromStorage() {
      // get all localStorage ids for form models
      let ids = Object.keys(localStorage);
          ids = ids.filter(key => key.startsWith("kirby$form$"));

      // load the model from localStorage for each id
      this.storage = ids.map(key => {
        return {
          ...JSON.parse(localStorage.getItem(key)),
          id: key.split("kirby$form$")[1]
        };
      });

      // filter models that do not have any changes
      this.storage = this.storage.filter(data => {
        return Object.keys(data.changes || {}).length > 0
      });
    },
    toggle() {
      this.isOpen = !this.isOpen;

      if (this.isOpen === true) {
        this.loadFromApi().then(() => {
          this.$refs.list.toggle();
        });
      } else {
        this.$refs.list.toggle();
      }
    }
  }
};
</script>

<style lang="scss">

.k-form-indicator-icon {
  color: $color-notice-on-dark;
}

.k-form-indicator-info {
  font-size: $font-size-small;
  font-weight: $font-weight-bold;
  padding: .75rem 1rem .25rem;
  line-height: 1.25em;
  width: 15rem;
}

</style>
