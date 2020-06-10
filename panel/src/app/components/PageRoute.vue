<template>
  <k-page-view
    v-bind="view"
    v-on="listeners"
    @changedStatus="onChangedStatus"
    @changedTemplate="onChangedTemplate"
    @changedTitle="onChangedTitle"
    @deleted="onDeleted"
    @reload="onReload"
  />
</template>
<script>
import ModelRoute from "./ModelRoute.vue";
import Vue from "vue";

const load = async (id) => {
  return await Vue.$api.pages.get(id, { view: "panel" });
};

export default {
  extends: ModelRoute,
  async beforeRouteEnter(to, from, next) {
    const model = await load(to.params.id);
    next(vm => vm.load(model));
  },
  async beforeRouteUpdate(to, from, next) {
    // do not reload if only tab hash has changed
    if (to.path !== from.path) {
      const model = await load(to.params.id);
      this.load(model);
    }
    next();
  },
  computed: {
    next() {
      if (!this.model.next) return false;

      return {
        link: this.$model.pages.link(this.model.next.id),
        tooltip: this.model.next.title
      };
    },
    prev() {
      if (!this.model.prev) return false;

      return {
        link: this.$model.pages.link(this.model.prev.id),
        tooltip: this.model.prev.title
      };
    },
    storeId() {
      return this.$model.pages.storeId(this.model.id);
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
      if (!this.model) {
        return {};
      }

      return {
        ...this.viewDefaults,
        api:        this.$model.pages.url(this.model.id),
        breadcrumb: this.$model.pages.breadcrumb(this.model),
        id:         this.$model.pages.id(this.model.id),
        next:       this.next,
        options:    this.$model.pages.dropdown(this.model.options),
        prev:       this.prev,
        preview:    this.model.previewUrl,
        rename:     this.model.options.changeTitle,
        status:     this.status,
        template:   this.model.blueprint.title,
        title:      this.model.title
      };
    }
  },
  methods: {
    onChangedSlug(page) {
      // Redirect, if slug was changed in default language
      if (
        !this.$store.state.languages.current ||
        this.$store.state.languages.current.default === true
      ) {
        const path = this.$model.pages.link(page.id);
        this.$router.push(path);
      }
    },
    onChangedStatus(page) {
      this.model.status = page.status;
      this.reload();
    },
    onChangedTemplate(page) {
      this.model.template = page.template;
      this.reload();
    },
    onChangedTitle(page) {
      this.model.title = page.title;
      this.reload();
      this.onTitle();
    },
    onDeleted() {
      if (this.model.parent) {
        const path = this.$model.pages.link(this.model.parent.id);
        this.$router.push(path);
      } else {
        const path = this.$model.pages.link();
        this.$router.push(path);
      }
    },
    onReload() {
      this.reload();
    },
    onTitle() {
      this.$model.system.title(this.model.title);
    },
    async reload() {
      const model = await load(this.model.id);
      this.load(model);
    },
    async save() {
      return await this.$model.pages.update(this.model.id);
    }
  }
}
</script>
