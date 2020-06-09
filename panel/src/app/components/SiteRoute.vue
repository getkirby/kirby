<template>
  <k-site-view
    v-bind="view"
    v-on="listeners"
    @changeTitle="onChangeTitle"
  />
</template>
<script>
import ModelRoute from "./ModelRoute.vue";
import Vue from "vue";

const load = async () => {
  return await Vue.$api.site.get();
};

export default {
  extends: ModelRoute,
  async beforeRouteEnter(to, from, next) {
    const model = await load();
    next(vm => vm.load(model));
  },
  async beforeRouteUpdate(to, from, next) {
    // do not reload if only tab hash has changed
    if (to.path !== from.path) {
      const model = await load();
      this.load(model);
    }
    next();
  },
  computed: {
    storeId() {
      return this.$model.site.storeId();
    },
    view() {

      if (!this.model) {
        return {};
      }

      return {
        ...this.viewDefaults,
        options: [],
        preview: this.model.previewUrl,
        rename:  this.model.options.changeTitle,
        title:   this.model.title
      };
    }
  },
  methods: {
    onChangeTitle(site) {
      this.model.title = site.title;
    },
    async reload() {
      const model = await load();
      this.load(model);
    },
    async save() {
      return await this.$model.site.update();
    },
    onTitle() {
      this.$model.system.title(this.$store.state.system.site);
    },
  }
}
</script>
