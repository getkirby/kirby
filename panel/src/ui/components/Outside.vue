<template>
  <div
    :data-loading="loading"
    :data-translation="translation"
    :data-translation-default="defaultTranslation"
    class="k-panel k-panel-outside">
    <!-- Main View -->
    <main class="k-panel-view">
      <slot />
    </main>
  </div>
</template>

<script>
export default {
  props: {
    defaultTranslation: {
      type: Boolean,
      default: false
    },
    loading: {
      type: Boolean,
      default: false
    },
    translation: {
      type: [Boolean, String],
      default: false
    }
  }
};
</script>

<style lang="scss">
.k-panel-outside {
  min-height: 100vh;
}
</style>
