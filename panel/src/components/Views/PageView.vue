<template>
  <k-inside>
    <k-view :data-locked="isLocked" class="k-page-view">
      <k-header
        :editable="permissions.changeTitle && !isLocked"
        :tab="tab.name"
        :tabs="tabs"
        @edit="action('rename')"
      >
        {{ page.title }}
        <template #left>
          <k-button-group>
            <k-button
              v-if="permissions.preview && page.previewUrl"
              :responsive="true"
              :link="page.previewUrl"
              target="_blank"
              icon="open"
            >
              {{ $t('open') }}
            </k-button>
            <k-status-icon
              v-if="status"
              :status="page.status"
              :disabled="!permissions.changeStatus || isLocked"
              :responsive="true"
              :text="status.label"
              @click="action('status')"
            />
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
        </template>

        <template #right>
          <k-prev-next
            v-if="page.id"
            :prev="prev"
            :next="next"
          />
        </template>
      </k-header>

      <k-sections
        :blueprint="blueprint"
        :empty="$t('page.blueprint', { template: blueprint })"
        :parent="$api.pages.url(page.id)"
        :tab="tab"
      />

      <k-page-rename-dialog ref="rename" @success="$reload" />
      <k-page-duplicate-dialog ref="duplicate" />
      <k-page-status-dialog ref="status" @success="$reload" />
      <k-page-template-dialog ref="template" @success="$reload" />
      <k-page-remove-dialog ref="remove" @success="onRemove" />
    </k-view>
  </k-inside>
</template>

<script>
// TODO: can we delete mixins/view/prevnext ?
import ModelView from "./ModelView.vue";

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
  computed: {
    options() {
      return async ready => {
        const options = await this.$api.pages.options(this.page.id);
        ready(options);
      };
    }
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
  methods: {
    action(action) {
      switch (action) {
        case "duplicate":
          this.$refs.duplicate.open(this.page.id);
          break;
        case "rename":
          this.$refs.rename.open(this.page.id, this.permissions, "title");
          break;
        case "url":
          this.$refs.rename.open(this.page.id, this.permissions, "slug");
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
