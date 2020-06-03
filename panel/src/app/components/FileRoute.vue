<template>
  <k-file-view
    v-if="model.filename"
    v-bind="view"
    @language="onLanguage"
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
    preview() {
      return {
        dimensions:  this.model.dimensions,
        icon:        this.model.icon,
        image:       this.model.url,
        link: {
          url: this.model.url
        },
        mime:        this.model.mime,
        size:        this.model.niceSize,
        template:    this.model.blueprint.name
      };
    },
    storeId() {
      return this.$models.file.storeId(this.id);
    },
    view() {
      return {
        ...this.viewDefaults,
        breadcrumb: this.$model.files.breadcrumb(this.model),
        filename:   this.filename,
        options:    this.$model.files.dropdown(this.model.options),
        parent:     this.parent,
        preview:    this.preview,
        rename:     true,
        url:        this.model.url,
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
    async saveModel() {
      return await this.$model.files.update(this.parent, this.filename);
    },
  }
}
</script>
