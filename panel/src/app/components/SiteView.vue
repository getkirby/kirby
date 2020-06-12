<template>
  <k-inside
    :languages="true"
    class="k-site-view"
    view="site"
  >
    <!-- blueprint -->
    <k-model-view
      v-bind="$props"
      :prevnext="false"
      api="site"
      @rename="$refs.rename.open()"
      v-on="$listeners"
    >
      <template v-slot:options>
        <k-button
          v-if="preview"
          :responsive="true"
          :link="preview"
          :text="$t('open')"
          target="_blank"
          icon="open"
        />
      </template>
      <template v-slot:empty>
        <k-box
          :text="$t('site.blueprint', { role: role })"
          theme="info"
        />
      </template>
    </k-model-view>

    <!-- dialogs -->
    <k-site-rename-dialog
      ref="rename"
      @success="$emit('changeTitle', $event)"
    />
  </k-inside>
</template>

<script>
import ModelView from "./ModelView.vue";

export default {
  props: {
    ...ModelView.props,
    preview: {
      type: [Boolean, String],
      default: "/",
    }
  }
}
</script>
