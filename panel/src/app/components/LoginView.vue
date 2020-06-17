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
        <k-login-form
          :processing="processing"
          v-on="$listeners"
        />
      </template>
    </k-view>
  </k-outside>
</template>

<script>
import LoginForm from "./LoginForm"
import Vue from "vue";

export default {
  components: {
    "k-login-form": Vue.$plugins.login || LoginForm
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
