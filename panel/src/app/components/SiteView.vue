<template>
  <k-inside
    :breadcrumb="breadcrumb"
    class="k-site-view"
    view="site"
  >
    <!-- blueprint -->
    <k-model-view
      v-bind="$props"
      :rename="true"
      :title="site.title"
      @rename="$refs.rename.open()"
    >
      <template v-slot:options>
        <k-button
          :responsive="true"
          :link="site.previewUrl"
          :text="$t('open')"
          target="_blank"
          icon="open"
        />
      </template>
    </k-model-view>

    <!-- dialogs -->
    <k-site-rename-dialog
      ref="rename"
      @success="onRenamed"
    />
  </k-inside>
</template>

<script>
import ModelView from "./ModelView.vue";

export default {
  props: {
    ...ModelView.props,
    site: {
      type: Object,
      default() {
        return {
          previewUrl: "/",
          title: this.$t("view.site"),
        }
      }
    }
  },
  computed: {
    breadcrumb() {
      return [
        {
          icon: "home",
          text: "Site"
        }
      ]
    }
  },
  methods: {
    onRenamed() {

    }
  }
}
</script>
