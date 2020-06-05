<template>
  <k-file-view
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
import Vue from "vue";

export default {
  extends: ModelRoute,
  props: {
    filename: {
      type: String
    },
    parent: {
      type: String
    },
    type: {
      type: String,
      default: "pages"
    }
  },
  async beforeRouteEnter(to, from, next) {
    const file = await Vue.$api.files.get(to.params.parentType + "/" + to.params.parentId, to.params.filename, { view: "panel" });

    next(vm => {
      return vm.load(file);
    });
  },
  async beforeRouteUpdate(to, from, next) {
    const file = await Vue.$api.pages.get(to.params.parentType + "/" + to.params.parentId, to.params.filename, { view: "panel" });
    this.load(file);
    next();
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
      return this.$model.files.storeId(this.id);
    },
    view() {

      if (!this.model) {
        return {};
      }

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
