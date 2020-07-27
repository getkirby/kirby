<template>
  <k-inside>
    <k-view :data-locked="isLocked" class="k-site-view">
      <k-header
        :tabs="tabs"
        :tab="tab"
        :editable="permissions.changeTitle && !isLocked"
        @edit="$refs.rename.open()"
        >
          {{ site.title }}
          <k-button-group slot="left">
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
      </k-header>
      <k-sections
        :blueprint="blueprint"
        :columns="tab.columns"
        parent="site"
      />
    </k-view>
    <k-site-rename-dialog ref="rename" @success="$reload" />
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
