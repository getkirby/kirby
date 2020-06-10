<template>
  <k-model-section
    ref="section"
    v-bind="$props"
    :empty="emptyOptions"
    :items="pages"
    :options="optionOptions"
    type="pages"
    @flag="onOption('status', $event)"
    @option="onOption"
  >
    <template v-slot:footer>
      <!-- Dialogs -->
      <k-page-create-dialog ref="createDialog" />
      <k-page-duplicate-dialog ref="duplicateDialog" />
      <k-page-remove-dialog ref="removeDialog" />
      <k-page-rename-dialog ref="renameDialog" />
      <k-page-slug-dialog ref="slugDialog" />
      <k-page-status-dialog ref="statusDialog" />
      <k-page-template-dialog ref="templateDialog" />
    </template>
  </k-model-section>
</template>

<script>
import ModelSection from "./ModelSection.vue";

export default {
  extends: ModelSection,
  created() {
    this.$events.$on("page.create", this.reload);
    this.$events.$on("page.delete", this.reload);
    this.$events.$on("page.modify", this.reload);
  },
  destroyed() {
    this.$events.$off("page.create", this.reload);
    this.$events.$off("page.delete", this.reload);
    this.$events.$off("page.modify", this.reload);
  },
  computed: {
    emptyDefaults() {
      return {
        icon: "page",
        text: this.$t("pages.empty")
      };
    },
    pages() {
      return async () => {
        const response = await this.load();

        const items = response.data.map(page => {
          const isEnabled = page.permissions.changeStatus !== false;

          page.flag = {
            icon: this.$model.pages.statusIcon(page.status),
            tooltip: isEnabled
              ? `${this.$t("page.status")}: ${page.status}`
              : `${this.$t("page.status")}: ${page.status} (${this.$t("disabled")})`,
            disabled: !isEnabled,
          };

          page.options = async ready => ready(await this.$model.pages.options(page.id, "list"));

          return page;
        });

        return {
          data: items,
          pagination: response.pagination
        };
      }
    },
    optionOptions() {
      if (this.add === false) {
        return [];
      }

      return [
        { icon: "add", option: "create", text: this.$t("add") },
      ];
    }
  },
  methods: {
    onOption(option, page = {}, pageIndex) {
      const dialog = this.$refs[option + "Dialog"];
      if (dialog) {
        return dialog.open(page.id);
      }
      throw "The option does not exist";
    }
  }
};
</script>
