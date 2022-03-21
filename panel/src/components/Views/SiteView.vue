<template>
  <k-inside>
    <k-view
      :data-locked="isLocked"
      data-id="/"
      data-template="site"
      class="k-site-view"
    >
      <k-header
        :editable="permissions.changeTitle && !isLocked"
        :tabs="tabs"
        :tab="tab.name"
        @edit="$dialog('site/changeTitle')"
      >
        {{ model.title }}
        <template #left>
          <k-button-group>
            <k-button
              :link="model.previewUrl"
              :responsive="true"
              :text="$t('open')"
              icon="open"
              target="_blank"
              class="k-site-view-preview"
            />
            <k-languages-dropdown />
          </k-button-group>
        </template>
      </k-header>

      <k-sections
        :blueprint="blueprint"
        :empty="$t('site.blueprint')"
        :lock="lock"
        :tab="tab"
        parent="site"
        @submit="$emit('submit', $event)"
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
  computed: {
    protectedFields() {
      return ["title"];
    }
  }
};
</script>
