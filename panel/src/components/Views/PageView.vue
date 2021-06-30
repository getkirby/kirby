<template>
  <k-inside :lock="lock">
    <k-view :data-locked="isLocked" class="k-page-view">
      <k-header
        :editable="permissions.changeTitle && !isLocked"
        :tab="tab.name"
        :tabs="tabs"
        @edit="$dialog($view.path + '/changeTitle')"
      >
        {{ model.title }}
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
              @click="$dialog($view.path + '/changeStatus')"
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
    </k-view>
  </k-inside>
</template>

<script>
import ModelView from "./ModelView.vue";

export default {
  extends: ModelView,
  props: {
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
  }
};
</script>
