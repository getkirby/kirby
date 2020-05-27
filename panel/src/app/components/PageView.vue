<template>
  <k-inside class="k-page-view">
    <k-model-view
      :columns="columns"
      :rename="true"
      :tab="tab"
      :tabs="tabs"
      :title="page.title"
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
          :responsive="true"
          v-bind="statusBtn"
          @click="onOption('status')"
          class="k-status-button"
        />
        <k-button
          :responsive="true"
          v-bind="templateBtn"
          @click="onOption('template')"
        />
        <k-dropdown>
          <k-button
            :disabled="isLocked"
            :responsive="true"
            :text="$t('settings')"
            icon="cog"
            @click="$refs.settings.toggle()"
          />
          <k-dropdown-content
            ref="settings"
            :options="options"
            @option="onOption"
          />
        </k-dropdown>
      </template>
    </k-model-view>

    <!-- Dialogs -->
    <k-page-duplicate-dialog
      ref="duplicate"
      @success="$emit('duplicate')"
    />
    <k-page-remove-dialog
      ref="remove"
      @success="$emit('remove')"
    />
    <k-page-rename-dialog
      ref="rename"
      @success="$emit('update')"
    />
    <k-page-slug-dialog
      ref="slug"
      @success="$emit('slug', $event)"
    />
    <k-page-status-dialog
      ref="status"
      @success="$emit('update')"
    />
    <k-page-template-dialog
      ref="template"
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
     options() {
      return async (ready) => {
        return ready(await this.$model.pages.options(this.page.id));
      };
    },
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
      switch (option) {
        case "duplicate":
          return this.$refs.duplicate.open(this.page.id);
        case "remove":
          return this.$refs.remove.open(this.page.id);
        case "rename":
          return this.$refs.rename.open(this.page.id);
        case "slug":
          return this.$refs.slug.open(this.page.id);
        case "status":
          return this.$refs.status.open(this.page.id);
        case "template":
          return this.$refs.template.open(this.page.id);
      }
    }
  }
};
</script>
