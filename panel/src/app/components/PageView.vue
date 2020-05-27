<template>
  <k-inside class="k-page-view">
    <k-model-view
      :columns="columns"
      :is-locked="isLocked"
      :options="options"
      :rename="true"
      :tab="tab"
      :tabs="tabs"
      :title="page.title"
      @option="onOption"
      @rename="onOption('rename')"
    >
      <template v-slot:options>
        <k-button
          :responsive="true"
          :text="$t('open')"
          icon="open"
          @click="onOpen"
        />
        <k-button
          :disabled="isLocked"
          :responsive="true"
          v-bind="statusBtn"
          @click="onOption('status')"
          class="k-status-button"
        />
        <k-button
          :disabled="isLocked"
          :responsive="true"
          v-bind="templateBtn"
          @click="onOption('template')"
        />
      </template>
    </k-model-view>

    <!-- Dialogs -->
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
  </k-inside>
</template>

<script>
export default {
  props: {
    columns: {
      type: Array,
      default() {
        return [];
      }
    },
    isLocked: {
      type: Boolean,
      default: false
    },
    options: {
      type: Array,
      default() {
        return [];
      }
    },
    page: {
      type: Object,
      default() {
        return {};
      }
    },
    tabs: {
      type: Array,
      default() {
        return []
      }
    },
    tab: {
      type: String,
      default: ""
    }
  },
  computed: {
    statusBtn() {
      const status = this.page.blueprint.status[this.page.status];
      const text   = status ? status.text : this.page.status;

      return {
        text: text,
        tooltip: `${this.$t("page.status")}: ${text}`,
        icon: {
          type: status.icon,
          color: status.color,
          size: "small"
        }
      };
    },
    templateBtn() {
      const text = this.page.blueprint.title || this.page.template;

      return {
        text: text,
        tooltip: `${this.$t("template")}: ${text}`,
        icon: {
          type: 'template',
          size: 'small'
        },
      };
    }
  },
  methods: {
    onOpen() {

    },
    onOption(option) {
      this.$refs[option + "Dialog"].open(this.page.id);
    }
  }
};
</script>
