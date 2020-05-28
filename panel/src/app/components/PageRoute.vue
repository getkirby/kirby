<template>
  <k-page-view
    v-if="page.id"
    v-bind="view"
    @changeStatus="onChangeStatus"
    @changeTitle="onChangeTitle"
    @delete="onDelete"
    @input="onInput"
    @revert="onRevert"
    @save="onSave"
  />
</template>
<script>
export default {
  props: {
    id: {
      type: String
    },
    tab: {
      type: String,
      default: "main"
    }
  },
  data() {
    return {
      original: {},
      page: {},
      view: {},
    };
  },
  created() {
    this.load();
  },
  watch: {
    "$route": "load",
  },
  methods: {
    columns(tabs, currentTab) {
      const tab = tabs.find(tab => tab.name === currentTab) || tabs[0];
      return tab.columns || {};
    },
    async load() {
      this.page = await this.$api.pages.get(this.id);
      this.original = this.$helper.clone(this.page.content);
      this.view = {
        changes: false,
        columns: this.columns(this.page.blueprint.tabs, this.tab),
        id: this.id,
        options: this.$model.pages.dropdown(this.page.options),
        preview: this.page.previewUrl,
        rename: this.page.options.changeTitle,
        status: this.status(this.page),
        tabs: this.page.blueprint.tabs,
        tab: this.tab,
        template: this.page.blueprint.title,
        title: this.page.title,
        value: this.page.content
      };
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
      this.view.status = this.status(page);
    },
    onChangeTitle(page) {
      this.view.title = page.title;
    },
    onDelete() {
      if (this.page.parent) {
        const path = this.$model.pages.link(this.page.parent.id);
        this.$router.push(path);
      } else {
        this.$router.push("/pages");
      }
    },
    onInput(value) {
      this.view.changes = JSON.stringify(this.original) != JSON.stringify(value);
      this.view.value   = value;
    },
    onRevert() {
      this.view.value = this.$helper.clone(this.original);
      this.view.changes = false;
    },
    async onSave() {
      await this.$model.pages.update(this.id, this.view.value);
      this.original = this.$helper.clone(this.view.value);
      this.view.changes = false;
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
