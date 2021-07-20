<template>
  <k-inside :lock="lock">
    <k-view
      :data-locked="isLocked"
      :data-id="model.id"
      :data-template="blueprint"
      class="k-page-view"
    >
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
              class="k-page-view-preview"
              icon="open"
              target="_blank"
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
            <k-dropdown class="k-page-view-options">
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
                :options="$dropdown($view.path)"
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
        :empty="$t('page.blueprint', { blueprint: $esc(blueprint) })"
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
    }
  }
};
</script>
