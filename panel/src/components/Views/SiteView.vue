<template>
  <k-inside>
    <k-view
      :data-locked="isLocked"
      class="k-site-view"
    >
      <k-header
        :editable="permissions.changeTitle && !isLocked"
        @edit="$refs.rename.open()"
      >
        {{ site.title }}
        <template #left>
          <k-button-group>
            <k-button
              :responsive="true"
              :link="site.previewUrl"
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
        :blueprint="site.blueprint.name"
        :empty="$t('site.blueprint')"
        :tab="tab"
        :tabs="tabs"
        parent="site"
        @submit="$emit('submit', $event)"
      />

      <k-site-rename-dialog ref="rename" @success="$reload" />
    </k-view>
  </k-inside>
</template>

<script>
import ModelView from "./ModelView";

export default {
  extends: ModelView,
  props: {
    site: {
      type: Object,
      default() {
        return {};
      }
    }
  }
};
</script>
