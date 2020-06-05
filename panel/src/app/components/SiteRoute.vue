<template>
  <k-site-view
    v-bind="view"
    @changeTitle="onChangeTitle"
    @input="onInput"
    @language="onLanguage"
    @revert="onRevert"
    @save="onSave"
  />
</template>
<script>
import ModelRoute from "./ModelRoute.vue";
import Vue from "vue";

export default {
  extends: ModelRoute,
  async beforeRouteEnter(to, from, next) {
    const site = await Vue.$api.site.get({ view: "panel" });

    next(vm => {
      return vm.load(site);
    });
  },
  async beforeRouteUpdate(to, from, next) {
    const site = await Vue.$api.site.get({ view: "panel" });
    this.load(site);
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
    async saveModel() {
      return await this.$model.site.update();
    }
  }
}
</script>
