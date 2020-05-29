<template>
  <k-file-view
    v-if="model.filename"
    v-bind="view"
    @remove="onRemove"
    @rename="onRename"
    @replace="onReplace"
    @input="onInput"
    @revert="onRevert"
    @save="onSave"
  />
</template>
<script>
import ModelRoute from "./ModelRoute.vue";

export default {
  extends: ModelRoute,
  props: {
    parent: {
      type: String
    },
    filename: {
      type: String
    }
  },
  computed: {
    id() {
      return this.parent + "/" + this.filename;
    },
    storeId() {
      return "/files/" + this.id;
    },
    view() {
      console.log(this.model);
      return {
        breadcrumb: this.$model.files.breadcrumb(this.model),
        changes:    this.changes,
        columns:    this.columns(this.model.blueprint.tabs, this.tab),
        filename:   this.model.filename,
        options:    this.$model.files.dropdown(this.model.options),
        parent:     this.model.parent.id,
        preview:    {
          ...this.model,
          ...this.model.dimensions || {},
          image: this.model.url,
          link:  this.model.url,
          size:  this.model.niceSize,
        },
        rename:     true,
        saving:     this.saving,
        tabs:       this.model.blueprint.tabs,
        tab:        this.tab,
        url:        this.model.url,
        value:      this.values,
        view:       "site"
      };
    }
  },
  methods: {
    async loadModel() {
      return await this.$api.files.get(this.parent, this.filename);
    },
    onRemove() {
      const path = this.$model.pages.link(this.parent);
      this.$router.push(path);
    },
    onRename(file) {
      const path = this.$model.files.link(this.parent, file.filename);
      this.$router.push(path);
    },
    onReplace(file) {
      this.load();
    },
    async saveModel(values) {
      return await this.$model.files.update(this.parent, this.filename, values);
    },
  }
}
</script>
