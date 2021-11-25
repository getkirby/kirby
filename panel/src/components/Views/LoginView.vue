<template>
  <k-panel>
    <k-view v-if="form === 'login'" align="center" class="k-login-view">
      <k-login-plugin :methods="methods" />
    </k-view>
    <k-view
      v-else-if="form === 'code'"
      align="center"
      class="k-login-code-view"
    >
      <k-login-code v-bind="$props" />
    </k-view>
  </k-panel>
</template>

<script>
import LoginForm from "../Forms/Login.vue";

export default {
  components: {
    "k-login-plugin": window.panel.plugins.login || LoginForm
  },
  props: {
    methods: Array,
    pending: Object
  },
  computed: {
    form() {
      if (this.pending.email) {
        return "code";
      }

      if (!this.$user) {
        return "login";
      }

      return null;
    }
  },
  created() {
    this.$store.dispatch("content/clear");
  }
};
</script>

<style>
.k-login-fields {
  position: relative;
}

.k-login-toggler {
  position: absolute;
  top: 0;
  inset-inline-end: 0;
  z-index: 1;

  text-decoration: underline;
  font-size: 0.875rem;
}

.k-login-form label abbr {
  visibility: hidden;
}

.k-login-buttons {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  padding: 1.5rem 0;
}

.k-login-button {
  padding: 0.5rem 1rem;
  font-weight: 500;
  transition: opacity 0.3s;
  margin-inline-end: -1rem;
}

.k-login-button span {
  opacity: 1;
}

.k-login-button[disabled] {
  opacity: 0.25;
}

.k-login-back-button,
.k-login-checkbox {
  display: flex;
  align-items: center;
  flex-grow: 1;
}

.k-login-back-button {
  margin-inline-start: -1rem;
}

.k-login-checkbox {
  padding: 0.5rem 0;
  font-size: var(--text-sm);
  cursor: pointer;
}

.k-login-checkbox .k-checkbox-text {
  opacity: 0.75;
  transition: opacity 0.3s;
}

.k-login-checkbox:hover span,
.k-login-checkbox:focus span {
  opacity: 1;
}
</style>
