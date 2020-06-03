<template>
  <k-site-view
    v-if="model.title"
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

export default {
  extends: ModelRoute,
  computed: {
    storeId() {
      return "/site";
    },
    view() {
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
    async loadModel() {
      return await this.$api.site.get({ view: "panel" });
    },
    onChangeTitle(site) {
      this.model.title = site.title;
    },
    async saveModel(values) {
      return await this.$model.site.update(values);
    }
  }
}
</script>
