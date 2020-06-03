<template>
  <k-model-section
    v-bind="$props"
    :empty="emptyOptions"
    :options="optionOptions"
    type="pages"
    @option="onOption"
  >
    <template v-slot:footer>
      <!-- Dialogs -->
      <k-page-create-dialog
        ref="createDialog"
        @success="$emit('create')"
      />
      <k-page-duplicate-dialog
        ref="duplicateDialog"
        @success="$emit('duplicate')"
      />
      <k-page-remove-dialog
        ref="removeDialog"
        @success="$emit('remove')"
      />
      <k-page-rename-dialog
        ref="renameDialog"
        @success="$emit('update')"
      />
      <k-page-slug-dialog
        ref="slugDialog"
        @success="$emit('slug', $event)"
      />
      <k-page-status-dialog
        ref="statusDialog"
        @success="$emit('update')"
      />
      <k-page-template-dialog
        ref="templateDialog"
        @success="$emit('update')"
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
      this.$refs[option + "Dialog"].open(page.id);
    }
  }
};
</script>
