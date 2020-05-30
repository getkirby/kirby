<template>
  <k-page-view
    v-if="model.id"
    v-bind="view"
    @changeStatus="onChangeStatus"
    @changeTitle="onChangeTitle"
    @delete="onDelete"
    @input="onInput"
    @language="onLanguage"
    @revert="onRevert"
    @save="onSave"
  />
</template>
<script>
import ModelRoute from "./ModelRoute.vue";

export default {
  extends: ModelRoute,
  props: {
    id: {
      type: String
    }
  },
  computed: {
    storeId() {
      return "/pages/" + this.id;
    },
    status() {
      const defaults  = this.$model.pages.statusIcon(this.model.status);
      const blueprint = this.model.blueprint.status[this.model.status];

      return {
        icon: {
          type: blueprint.icon   || defaults.type,
          color: blueprint.color || defaults.color,
          size: "small",
        },
        text: blueprint.label,
        tooltip: blueprint.text,
      };
    },
    view() {
      return {
        ...this.viewDefaults,
        breadcrumb: this.$model.pages.breadcrumb(this.model),
        id:         this.id,
        options:    this.$model.pages.dropdown(this.model.options),
        preview:    this.model.previewUrl,
        rename:     this.model.options.changeTitle,
        status:     this.status,
        template:   this.model.blueprint.title,
        title:      this.model.title
      };
    }
  },
  methods: {
    async loadModel() {
      return await this.$api.pages.get(this.id);
    },
    onChangeSlug(page) {
      // Redirect, if slug was changed in default language
      if (
        !this.$store.state.languages.current ||
        this.$store.state.languages.current.default === true
      ) {
        const path = this.$model.pages.link(page.id);
        this.$router.push(path);
      }
    },
    onChangeStatus(page) {
      this.model.status = page.status;
    },
    onChangeTitle(page) {
      this.model.title = page.title;
    },
    onDelete() {
      if (this.model.parent) {
        const path = this.$model.pages.link(this.model.parent.id);
        this.$router.push(path);
      } else {
        this.$router.push("/pages");
      }
    },
    async saveModel(values) {
      return await this.$model.pages.update(this.id, values);
    }
  }
}
</script>
