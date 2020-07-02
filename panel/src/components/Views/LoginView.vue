<template>
  <k-error-view v-if="issue">
    {{ issue.message }}
  </k-error-view>
  <k-view v-else-if="ready" align="center" class="k-login-view">
    <k-login-form />
  </k-view>
</template>

<script>
import LoginForm from "../Forms/Login.vue";

export default {
  components: {
    "k-login-form": window.panel.plugins.login || LoginForm
  },
  data() {
    return {
      ready: false,
      issue: null
    };
  },
  created() {
    this.$store.dispatch("content/current", null);
    this.$store
      .dispatch("system/load")
      .then(system => {
        if (!system.isReady) {
          this.$go("/installation");
        }

        if (system.user && system.user.id) {
          this.$go("/");
        }

        this.ready = true;
        this.$store.dispatch("title", this.$t("login"));
      })
      .catch(error => {
        this.issue = error;
      });
  }
};
</script>

<style lang="scss">
.k-login-form label abbr {
  visibility: hidden;
}

.k-login-buttons {
  display: flex;
  align-items: center;
  padding: 1.5rem 0;
}

.k-login-button {
  padding: 0.5rem 1rem;
  font-weight: 500;
  transition: opacity 0.3s;

  [dir="ltr"] & {
    margin-right: -1rem;
  }

  [dir="rtl"] & {
    margin-left: -1rem;
  }
}

.k-login-button span {
  opacity: 1;
}

.k-login-button[disabled] {
  opacity: 0.25;
}

.k-login-checkbox {
  display: flex;
  align-items: center;
  padding: 0.5rem 0;
  flex-grow: 1;
  font-size: $font-size-small;
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

.k-login-alert {
  padding: .5rem .75rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  min-height: 38px;
  margin-bottom: 2rem;
  background: $color-negative;
  color: #fff;
  font-size: $font-size-small;
  border-radius: $border-radius;
  box-shadow: $box-shadow;
  cursor: pointer;
}
</style>
