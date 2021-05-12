<template>
  <k-outside>
    <k-view v-if="form === 'login'" align="center" class="k-login-view">
      <k-login-plugin />
    </k-view>
    <k-view v-else-if="form === 'code'" align="center" class="k-login-code-view">
      <k-login-code />
    </k-view>
  </k-outside>
</template>

<script>
import LoginForm from "../Forms/Login.vue";

export default {
  components: {
    "k-login-plugin": window.panel.plugins.login || LoginForm
  },
  computed: {
    form() {
      // TODO: fix this
      // if (this.$user.pendingEmail) {
      //   return "code";
      // }
      
      if (!this.$user) {
        return "login";
      }

      return null;
    }
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
  right: 0;
  z-index: 1;

  text-decoration: underline;
  font-size: .875rem;
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
  padding: .5rem 1rem;
  font-weight: 500;
  transition: opacity .3s;
  margin-inline-end: -1rem
}

.k-login-button span {
  opacity: 1;
}

.k-login-button[disabled] {
  opacity: .25;
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
  padding: .5rem 0;
  font-size: var(--text-sm);
  cursor: pointer;
}

.k-login-checkbox .k-checkbox-text {
  opacity: .75;
  transition: opacity .3s;
}

.k-login-checkbox:hover span,
.k-login-checkbox:focus span {
  opacity: 1;
}

.k-login-alert {
  padding: .5rem .75rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  min-height: 38px;
  margin-bottom: 2rem;
  background: var(--color-negative);
  color: #fff;
  font-size: var(--text-sm);
  border-radius: var(--rounded-xs);
  box-shadow: var(--shadow-lg);
  cursor: pointer;
}
</style>
