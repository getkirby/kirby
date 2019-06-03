<template>
  <k-dropdown v-if="changes.length > 0" class="k-form-indicator">
    <k-button class="k-topbar-button" @click="$refs.list.toggle()">
      <k-icon type="edit" class="k-form-indicator-icon" />
    </k-button>

    <k-dropdown-content align="right" ref="list">
      <p class="k-form-indicator-info">
        {{ $t("lock.unsaved") }}:
      </p>
      <hr />
      <k-dropdown-item
        v-for="change in changes"
        :key="change.id"
        :icon="change.icon"
        :link="change.link"
      >
        {{ label(change) }}
      </k-dropdown-item>
    </k-dropdown-content>
  </k-dropdown>
</template>

<script>
export default {
  data() {
    return {
      changes: []
    }
  },
  computed: {
    models() {
      return this.$store.state.form.models;
    }
  },
  watch: {
    models: {
      handler() {
        this.load();
      },
      deep: true
    }
  },
  created() {
    this.load();
  },
  methods: {
    entry(model, stored) {
      if (stored.id.startsWith("pages/")) {
        return {
          icon: "page",
          link: this.$api.pages.link(model.id)
        };
      }

      if (stored.id.startsWith("files/")) {
        return {
          icon: "image",
          link: model.link
        };
      }

      if (stored.id.startsWith("users/")) {
        return {
          icon: "user",
          link: this.$api.users.link(model.id)
        };
      }
    },
    label(change) {
      return change.model.title || change.model.filename || change.model.email;
    },
    load() {
      let stored = this.loadStorage();

      // filter removed changes
      this.changes = this.changes.filter(change => {
        return stored.map(x => x.id).indexOf(change.id) !== -1;
      });

      // filter changes that have already been fetched
      stored = stored.filter(stored => {
        return this.changes.map(x => x.id).indexOf(stored.id) === -1;
      });

      let promises = stored.map(stored => {
        return this.$api.get(stored.api, { view: "compact" }, null, true).then(model => {
          let { icon, link } = this.entry(model, stored);
          return {
            id: stored.id,
            link: link,
            icon: icon,
            model: model
          };
        });
      });

      Promise.all(promises).then(models => {
        this.changes = [
          ...this.changes,
          ...models
        ];
      });
    },
    loadStorage() {
      return Object.keys(localStorage)
                   .filter(key => key.startsWith("kirby$form$"))
                   .map(key => {
        return {
          ...JSON.parse(localStorage.getItem(key)),
          id: key.split("kirby$form$")[1]
        };
      }).filter(data => {
        return Object.keys(data.changes).length > 0
      });
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
