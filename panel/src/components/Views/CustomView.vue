<template>
  <k-error-boundary :key="plugin">
    <component
      :is="'k-' + plugin + '-plugin-view'"
      v-bind="$props"
    />
    <template #error="{ error }">
      <k-error-view>
        {{ error.message || error }}
      </k-error-view>
    </template>
  </k-error-boundary>
</template>

<script>
export default {
  beforeRouteEnter(to, from, next) {
    next(vm => {
      vm.$store.dispatch("breadcrumb", []);
      vm.$store.dispatch("content/current", null);
    })
  },
  props: {
    plugin: String,
    hash: String
  }
};
</script>
