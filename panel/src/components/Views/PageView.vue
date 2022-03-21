<template>
  <k-inside>
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
        @edit="$dialog(id + '/changeTitle')"
      >
        {{ model.title }}
        <template #left>
          <k-button-group>
            <k-button
              v-if="permissions.preview && model.previewUrl"
              :link="model.previewUrl"
              :responsive="true"
              :text="$t('open')"
              icon="open"
              target="_blank"
              class="k-page-view-preview"
            />
            <k-status-icon
              v-if="status"
              :status="model.status"
              :disabled="!permissions.changeStatus || isLocked"
              :responsive="true"
              :text="status.label"
              @click="$dialog(id + '/changeStatus')"
            />
            <k-dropdown class="k-page-view-options">
              <k-button
                :disabled="isLocked === true"
                :responsive="true"
                :text="$t('settings')"
                icon="cog"
                @click="$refs.settings.toggle()"
              />
              <k-dropdown-content ref="settings" :options="$dropdown(id)" />
            </k-dropdown>

            <k-languages-dropdown />
          </k-button-group>
        </template>
        <template #right>
          <k-prev-next v-if="model.id" :prev="prev" :next="next" />
        </template>
      </k-header>
      <k-sections
        :blueprint="blueprint"
        :empty="$t('page.blueprint', { blueprint: $esc(blueprint) })"
        :lock="lock"
        :parent="id"
        :tab="tab"
      />
    </k-view>
    <template #footer>
      <k-form-buttons :lock="lock" />
    </template>
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
    protectedFields() {
      return ["title"];
    }
  }
};
</script>
