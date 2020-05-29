<template>
  <k-page-view
    v-if="model.id"
    v-bind="view"
    :saving="saving"
    @changeStatus="onChangeStatus"
    @changeTitle="onChangeTitle"
    @delete="onDelete"
    @input="onInput"
    @revert="onRevert"
    @save="onSave"
  />
</template>
<script>
import ModelRoute from "./ModelRoute.vue";

export default {
  extends: ModelRoute,
  computed: {
    storeId() {
      return "/pages/" + this.id;
    },
    view() {
      return {
        breadcrumb: this.$model.pages.breadcrumb(this.model),
        changes: this.changes,
        columns: this.columns(this.model.blueprint.tabs, this.tab),
        id: this.id,
        options: this.$model.pages.dropdown(this.model.options),
        preview: this.model.previewUrl,
        rename: this.model.options.changeTitle,
        status: this.status(this.model),
        tabs: this.model.blueprint.tabs,
        tab: this.tab,
        template: this.model.blueprint.title,
        title: this.model.title,
        value: this.values
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
      this.model.status = this.status(page);
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
    },
    status(page) {
      const icon = this.$model.pages.statusIcon(page.status);
      const status = page.blueprint.status[page.status];

      return {
        icon: {
          type: status.icon || icon.type,
          color: status.color || icon.color,
          size: "small",
        },
        text: status.label,
        tooltip: status.text,
      };
    },
  }
}
</script>
