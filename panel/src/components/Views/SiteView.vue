<template>
  <k-inside :lock="lock">
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
              :responsive="true"
              :link="model.previewUrl"
              class="k-site-view-opreviewptions"
              icon="open"
              target="_blank"
            >
              {{ $t('open') }}
            </k-button>
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
  </k-inside>
</template>

<script>
import ModelView from "./ModelView.vue";

export default {
  extends: ModelView,
  computed: {
    id() {
      return "site"
    }
  }
};
</script>
