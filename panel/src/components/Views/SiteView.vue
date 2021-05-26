<template>
  <k-inside :lock="lock">
    <k-view
      :data-locked="isLocked"
      class="k-site-view"
    >
      <k-header
        :editable="permissions.changeTitle && !isLocked"
        :tabs="tabs"
        :tab="tab.name"
        @edit="$refs.rename.open()"
      >
        {{ model.title }}
        <template #left>
          <k-button-group>
            <k-button
              :responsive="true"
              :link="model.previewUrl"
              target="_blank"
              icon="open"
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

      <k-site-rename-dialog ref="rename" @success="$reload" />
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
