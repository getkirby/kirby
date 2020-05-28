<template>
  <k-inside class="k-page-view">
    <k-model-view
      v-bind="$props"
      @option="onOption"
      @rename="onOption('rename')"
      v-on="$listeners"
    >
      <template v-slot:options>
        <k-button
          v-if="preview !== false"
          :responsive="true"
          :link="preview"
          :text="$t('open')"
          icon="open"
          @click="onOpen"
        />
        <k-button
          v-if="status !== false"
          v-bind="statusButton"
          class="k-status-button"
          @click="onOption('status')"
        />
        <k-button
          v-if="template !== false"
          v-bind="templateButton"
          @click="onOption('template')"
        />
      </template>
    </k-model-view>

    <!-- Dialogs -->
    <k-page-duplicate-dialog
      ref="duplicateDialog"
      @success="$emit('duplicate', $event)"
    />
    <k-page-remove-dialog
      ref="removeDialog"
      @success="$emit('delete')"
    />
    <k-page-rename-dialog
      ref="renameDialog"
      @success="$emit('changeTitle', $event)"
    />
    <k-page-slug-dialog
      ref="slugDialog"
      @success="$emit('changeSlug', $event)"
    />
    <k-page-status-dialog
      ref="statusDialog"
      @success="$emit('changeStatus', $event)"
    />
    <k-page-template-dialog
      ref="templateDialog"
      @success="$emit('changeTemplate', $event)"
    />
  </k-inside>
</template>

<script>
import ModelView from "./ModelView.vue";

export default {
  props: {
    ...ModelView.props,
    id: {
      type: String,
      required: true
    },
    preview: {
      type: [Boolean, String],
      default: false
    },
    status: {
      type: [Boolean, Object],
      default: false,
    },
    template: {
      type: [Boolean, String],
      default: false
    }
  },
  computed: {
    statusButton() {
      return {
        ...this.status,
        responsive: true,
        disabled: this.isDisabledOption("status", this.status.disabled || false),
      };
    },
    templateButton() {
      return {
        disabled: this.isDisabledOption("template"),
        icon: {
          type: "template",
          size: "small"
        },
        responsive: true,
        text: this.template,
        tooltip: `${this.$t("template")}: ${this.template}`,
      };
    }
  },
  methods: {
    isDisabledOption(name, fallback = false) {
      let option = this.options.filter(option => option.click === name)[0];

      if (!option || option.disabled === true) {
        return true;
      }

      if (this.lock !== false) {
        return true;
      }

      return fallback;
    },
    onOpen() {

    },
    onOption(option) {
      this.$refs[option + "Dialog"].open(this.id);
    }
  }
};
</script>
