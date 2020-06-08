<template>
  <k-file-view
    v-bind="view"
    v-on="listeners"
    @remove="onRemove"
    @rename="onRename"
    @replace="onReplace"
  />
</template>

<script>
import ModelRoute from "./ModelRoute.vue";
import Vue from "vue";

const load = async (parent, filename) => {
  let model = await Vue.$api.files.get(parent, filename, { view: "panel" });
  model.parent.guid = parent;
  return model;
};

export default {
  extends: ModelRoute,
  params: {
    filename: {
      type: String
    },
    parent: {
      type: String
    }
  },
  async beforeRouteEnter(to, from, next) {
    const model = await load(
      to.params.parentType + "/" + to.params.parentId,
      to.params.filename
    );
    next(vm => vm.load(model));
  },
  async beforeRouteUpdate(to, from, next) {
    const model = await load(
      to.params.parentType + "/" + to.params.parentId,
      to.params.filename
    );
    this.load(model);
    next();
  },
  computed: {
    id() {
      return this.model.parent.guid + "/" + this.model.filename;
    },
    next() {
      if (!this.model.nextWithTemplate) return false;

      return {
        link: this.model.nextWithTemplate.link,
        tooltip: this.model.nextWithTemplate.filename
      };
    },
    prev() {
      if (!this.model.prevWithTemplate) return false;

      return {
        link: this.model.prevWithTemplate.link,
        tooltip: this.model.prevWithTemplate.filename
      };
    },
    preview() {
      return {
        dimensions:  this.model.dimensions,
        icon:        this.model.icon,
        image:       this.model.url,
        link: { url: this.model.url },
        mime:        this.model.mime,
        size:        this.model.niceSize,
        template:    this.model.blueprint.name
      };
    },
    storeId() {
      return this.$model.files.storeId(
        this.model.parent.guid,
        this.model.filename
      );
    },
    view() {
      if (!this.model) {
        return {};
      }

      return {
        ...this.viewDefaults,
        api:        this.$model.files.url(
          this.model.parent.guid,
          this.model.filename
        ),
        breadcrumb: this.$model.files.breadcrumb(
          this.model,
          this.$route.params.parentType
        ),
        filename:   this.model.filename,
        mime:       this.model.mime,
        next:       this.next,
        options:    this.$model.files.dropdown(this.model.options),
        parent:     this.model.parent.guid,
        prev:       this.prev,
        preview:    this.preview,
        rename:     true,
        template:   this.model.template,
        url:        this.model.url,
        view:       "site"
      };
    }
  },
  methods: {
    onRemove() {
      const path = this.$model.pages.link(this.model.parent.id);
      this.$router.push(path);
    },
    onRename(file) {
      const path = this.$model.files.link(this.model.parent.guid, file.filename);
      this.$router.push(path);
    },
    onReplace() {
      this.reload();
    },
    onTitle() {
      this.$model.system.title(this.model.filename);
    },
    async reload() {
      const model = await load(this.model.parent.guid, this.model.filename);
      this.load(model);
    },
    async save() {
      return await this.$model.files.update(this.model.parent.guid, this.model.filename);
    },
  }
}
</script>
