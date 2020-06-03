<template>
  <k-model-section
    ref="section"
    v-bind="$props"
    :empty="emptyOptions"
    :items="pages"
    :options="optionOptions"
    type="pages"
    @option="onOption"
  >
    <template v-slot:footer>
      <!-- Dialogs -->
      <k-page-create-dialog
        ref="createDialog"
        @success="onChanged"
      />
      <k-page-duplicate-dialog
        ref="duplicateDialog"
        @success="onChanged"
      />
      <k-page-remove-dialog
        ref="removeDialog"
        @success="onChanged"
      />
      <k-page-rename-dialog
        ref="renameDialog"
        @success="onChanged"
      />
      <k-page-slug-dialog
        ref="slugDialog"
        @success="onChanged"
      />
      <k-page-status-dialog
        ref="statusDialog"
        @success="onChanged"
      />
      <k-page-template-dialog
        ref="templateDialog"
        @success="onChanged"
      />
    </template>
  </k-model-section>
</template>

<script>
import ModelSection from "./ModelSection.vue";

export default {
  extends: ModelSection,
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
    onChanged() {
      this.$refs.section.$refs.collection.reload();
    },
    onOption(option, page = {}, pageIndex) {
      this.$refs[option + "Dialog"].open(page.id);
    }
  }
};
</script>
