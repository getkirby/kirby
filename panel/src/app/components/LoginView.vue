<template>
  <k-outside
    :loading="loading || processing"
    class="k-loading-view"
  >
    <k-view align="center">
      <template v-if="loading">
        <k-loader />
      </template>
      <template v-else>
        <login-form
          :is="form"
          :processing="processing"
          v-on="$listeners"
        />
      </template>
    </k-view>
  </k-outside>
</template>

<script>
export default {
  components: {
    "login-form": window.panel.plugins.login || "k-login-form"
  },
  props: {
    loading: {
      type: Boolean,
      default: false
    },
    processing: {
      type: Boolean,
      default: false
    }
  }
}
</script>

<style>
.k-login-button {
  margin-right: -.75rem;
}
</style>
