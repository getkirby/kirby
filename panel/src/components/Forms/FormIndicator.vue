<template>
  <k-dropdown v-if="changes.length > 0" class="k-form-indicator">
    <k-button @click="$refs.list.toggle()">
      <k-icon type="edit" class="k-form-indicator-icon" />
    </k-button>

    <k-dropdown-content ref="list">
      <k-dropdown-item>
        <strong>{{ $t("lock.unsaved") }}</strong>
      </k-dropdown-item>
      <k-dropdown-item
        v-for="change in changes"
        :key="change.id"
        :link="change.link"
      >
        <k-icon :type="change.icon" />
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
          icon: "file-" + model.type,
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
[dir="ltr"] .k-form-indicator .k-dropdown-content {
  left: auto;
  right: 0;
}

[dir="rtl"] .k-form-indicator .k-dropdown-content {
  left: 0;
  right: auto;
}

.k-form-indicator .k-dropdown-content .k-button-text {
  display: flex;

  > .k-icon {
    margin-right: .5rem;
  }
}

.k-form-indicator-icon {
  color: $color-notice-on-dark;
}

</style>
