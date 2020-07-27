<template>
  <k-inside>
    <k-view :data-locked="isLocked" class="k-page-view">

      <k-header
        :tabs="tabs"
        :tab="tab"
        :editable="permissions.changeTitle && !isLocked"
        @edit="action('rename')"
      >
        {{ page.title }}
        <k-button-group slot="left">
          <k-button
            v-if="permissions.preview && page.previewUrl"
            :responsive="true"
            :link="page.previewUrl"
            target="_blank"
            icon="open"
          >
            {{ $t('open') }}
          </k-button>
          <k-button
            v-if="status"
            :class="['k-status-flag', 'k-status-flag-' + page.status]"
            :disabled="!permissions.changeStatus || isLocked"
            :icon="!permissions.changeStatus || isLocked ? 'protected' : 'circle'"
            :responsive="true"
            :tooltip="status.label"
            @click="action('status')"
          >
            {{ status.label }}
          </k-button>
          <k-dropdown>
            <k-button
              :responsive="true"
              :disabled="isLocked === true"
              icon="cog"
              @click="$refs.settings.toggle()"
            >
              {{ $t('settings') }}
            </k-button>
            <k-dropdown-content ref="settings" :options="options" @action="action" />
          </k-dropdown>
          <k-languages-dropdown />
        </k-button-group>
        <k-prev-next
          slot="right"
          :prev="prev"
          :next="next"
        />
      </k-header>

      <k-sections
        :blueprint="blueprint"
        :columns="tab.columns"
        :parent="$api.pages.url(page.id)"
      />

      <k-page-rename-dialog ref="rename" @success="$reload" />
      <k-page-duplicate-dialog ref="duplicate" />
      <k-page-url-dialog ref="url" />
      <k-page-status-dialog ref="status" @success="$reload" />
      <k-page-template-dialog ref="template" @success="$reload" />
      <k-page-remove-dialog ref="remove" @success="onRemove" />

    </k-view>
  </k-inside>
</template>

<script>
import ModelView from "./ModelView";

export default {
  extends: ModelView,
  props: {
    page: {
      type: Object,
      default() {
        return {}
      }
    },
    status: Object
  },
  watch: {
    "page.id": {
      handler() {
        this.$store.dispatch("content/create", {
          id: "pages/" + this.page.id,
          api: this.$api.pages.link(this.page.id),
          content: this.page.content
        });
      },
      immediate: true
    }
  },
  computed: {
    options() {
      return ready => {
        this.$api.pages.options(this.page.id).then(options => {
          ready(options);
        });
      };
    }
  },
  methods: {
    action(action) {
      switch (action) {
        case "duplicate":
          this.$refs.duplicate.open(this.page.id);
          break;
        case "rename":
          this.$refs.rename.open(this.page.id);
          break;
        case "url":
          this.$refs.url.open(this.page.id);
          break;
        case "status":
          this.$refs.status.open(this.page.id);
          break;
        case "template":
          this.$refs.template.open(this.page.id);
          break;
        case "remove":
          this.$refs.remove.open(this.page.id);
          break;
        default:
          this.$store.dispatch(
            "notification/error",
            this.$t("notification.notImplemented")
          );
          break;
      }
    },
    onRemove() {
      this.$go(this.page.parent);
    }
  }
};
</script>

<style lang="scss">
.k-status-flag svg {
  width: 14px;
  height: 14px;
}
.k-status-flag-listed .k-icon {
  color: $color-positive-on-dark;
}
.k-status-flag-unlisted .k-icon {
  color: $color-focus-on-dark;
}
.k-status-flag-draft .k-icon {
  color: $color-negative-on-dark;
}
.k-status-flag[disabled] {
  opacity: 1;
}
</style>
