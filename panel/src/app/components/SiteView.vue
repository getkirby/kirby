<template>
  <k-inside
    :breadcrumb="breadcrumb"
    class="k-site-view"
    view="site"
  >
    <!-- blueprint -->
    <k-model-view
      :columns="columns"
      :is-locked="isLocked"
      :rename="true"
      :tab="tab"
      :tabs="tabs"
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
export default {
  props: {
    columns: {
      type: Array,
      default() {
        return [];
      }
    },
    isLocked: {
      type: Boolean,
      default: false
    },
    site: {
      type: Object,
      default() {
        return {
          previewUrl: "/",
          title: this.$t("view.site"),
        }
      }
    },
    tab: {
      type: String
    },
    tabs: {
      type: Array,
      default() {
        return []
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
