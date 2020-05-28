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
      page: {}
    };
  },
  computed: {
    changes() {
      return this.$store.getters["content/hasChanges"]();
    },
    values() {
      return this.$store.getters["content/values"]();
    },
    view() {
      return {
        breadcrumb: this.$model.pages.breadcrumb(this.page),
        changes: this.changes,
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
        value: this.values
      };
    }
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
      await this.$store.dispatch("content/hasUnlock");
      this.$store.dispatch("content/create", this.page);
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
      this.page.status = this.status(page);
    },
    onChangeTitle(page) {
      this.page.title = page.title;
    },
    onDelete() {
      if (this.page.parent) {
        const path = this.$model.pages.link(this.page.parent.id);
        this.$router.push(path);
      } else {
        this.$router.push("/pages");
      }
    },
    onInput(values) {
      this.$store.dispatch("content/input", { id: this.id, values: values });
    },
    onRevert() {
      this.$store.dispatch("content/revert", this.id);
    },
    async onSave() {
      const values = this.$store.getters["content/values"](this.id);
      await this.$model.pages.update(this.id, values);
      this.$store.dispatch("content/update", { id: this.id, values: values });
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
