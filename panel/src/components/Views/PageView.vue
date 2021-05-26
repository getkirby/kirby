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
              v-if="permissions.preview && model.previewUrl"
              :responsive="true"
              :link="model.previewUrl"
              target="_blank"
              icon="open"
            >
              {{ $t('open') }}
            </k-button>
            <k-status-icon
              v-if="status"
              :status="model.status"
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
              <k-dropdown-content 
                ref="settings" 
                :options="options" 
                @action="action"
              />
            </k-dropdown>

            <k-languages-dropdown />
          </k-button-group>
        </template>

        <template #right>
          <k-prev-next
            v-if="model.id"
            :prev="prev"
            :next="next"
          />
        </template>
      </k-header>

      <k-sections
        :blueprint="blueprint"
        :empty="$t('page.blueprint', { template: blueprint })"
        :lock="lock"
        :parent="$api.pages.url(model.id)"
        :tab="tab"
      />

      <k-page-rename-dialog ref="rename" @success="$reload" />
      <k-page-duplicate-dialog ref="duplicate" />
      <k-page-status-dialog ref="status" @success="$reload" />
      <k-page-template-dialog ref="template" @success="$reload" />
      <k-page-remove-dialog ref="remove" @success="onRemove" />

      <k-form-buttons :lock="lock" />
    </k-view>
  </k-inside>
</template>

<script>
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
    id() {
      return "pages/" + this.model.id;
    },
    options() {
      return async ready => {
        const options = await this.$api.pages.options(this.model.id);
        ready(options);
      };
    }
  },
  methods: {
    action(action) {
      switch (action) {
        case "duplicate":
          this.$refs.duplicate.open(this.model.id);
          break;
        case "rename":
          this.$refs.rename.open(this.model.id, this.permissions, "title");
          break;
        case "url":
          this.$refs.rename.open(this.model.id, this.permissions, "slug");
          break;
        case "status":
          this.$refs.status.open(this.model.id);
          break;
        case "template":
          this.$refs.template.open(this.model.id);
          break;
        case "remove":
          this.$refs.remove.open(this.model.id);
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
      this.$go(this.model.parent);
    }
  }
};
</script>
