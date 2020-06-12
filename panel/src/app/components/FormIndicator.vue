<template>
  <k-dropdown
    v-if="changes.length"
    class="k-form-indicator"
  >
    <k-button
      icon="edit"
      color="orange-light"
      class="k-topbar-button"
      @click="$refs.dropdown.toggle()"
    />
    <k-dropdown-content
      ref="dropdown"
      :options="options"
      align="right"
      theme="light"
      @option="onOption"
    />
  </k-dropdown>
</template>

<script>
export default {
  props: {
    languages: {
      type: Object,
      default() {
        return {};
      }
    },
    models: {
      type: Object,
      default() {
        return {};
      }
    }
  },
  computed: {
    changes() {
      // make sure objects without changes are filtered
      // and add id to object
      return Object.keys(this.models).filter(id => {
        return this.models[id].changes &&
               Object.keys(this.models[id].changes).length > 0;
      }).map(id => {
        return {
          id: id,
          ...this.models[id]
        };
      });
    },
    options() {
      return async (ready) => {
        let options = [
          {
            text: this.$t("lock.unsaved"),
            disabled: true
          },
          "-"
        ];

        const changed = this.changes.map(async model => {
          const endpoint = this.$store.getters["content/api"](model.id);
          const response = await this.$api.get(endpoint, { view: "compact" });
          let option     = this.mapOption(model.id, response);

          if (this.languages.current) {
            const language  = model.id.split("/").pop();
            option.text     = option.text + " (" + language + ")";
            option.language = language;
          }

          return option;
        });

        return ready([
          ...options,
          ... await Promise.all(changed)
        ]);
      }
    }
  },
  methods: {
    mapOption(id, model) {
      if (id.includes("/files/") === true) {
        return {
          icon: "image",
          text: model.filename,
          to:   model.link
        };
      }

      if (id.startsWith("pages/") === true) {
        return {
          icon: "page",
          text: model.title,
          to:   this.$model.pages.link(model.id)
        };
      }

      if (id.startsWith("users/") === true) {
        return {
          icon: "user",
          text: model.name || model.email,
          to:   this.$model.users.link(model.id)
        };
      }

      if (id === "/") {
        return {
          icon: "home",
          text: model.title,
          to:   "/site"
        };
      }
    },
    onOption(click, option) {
      // if a language is set for an option
      // and it is not the current content language,
      // switch the content language before routing to model
      if (option.language) {
        if (option.language !== this.languages.current) {
          const language  = this.languages.all.filter(language => {
            return language.code === option.language;
          })[0];
          this.$store.dispatch("languages/current", language);
        }
      }

      this.$router.push(option.to);
    }
  }
}
</script>
