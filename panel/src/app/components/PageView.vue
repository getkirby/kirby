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
          icon="open"
          @click="on"
        >
          {{ $t("open") }}
        </k-button>
        <k-button
          :responsive="true"
          v-bind="statusFlag"
          @click="onOption('status')"
          class="k-status-button"
        >
          {{ page.status.text || $t('page.status') }}
        </k-button>
        <k-dropdown>
          <k-button
            :responsive="true"
            :disabled="isLocked"
            icon="cog"
            @click="$refs.settings.toggle()"
          >
            {{ $t('settings') }}
          </k-button>
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
      return async () => this.$model.pages.options(this.page.id);
    },
    statusFlag() {
      if (this.page.status.value === "unlisted") {
        return {
          icon: {
            type: 'circle-half',
            size: 'small',
            color: 'blue-light',
          },
        };
      }

      if (this.page.status.value === "listed") {
        return {
          icon: {
            type: 'circle',
            size: 'small',
            color: 'green-light',
          }
        };
      }

      return {
        icon: {
          type: 'circle-outline',
          size: 'small',
          color: 'red-light',
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
