<template>
  <k-error-view v-if="issue">
    {{ issue.message }}
  </k-error-view>
  <k-view v-else-if="ready && form === 'login'" align="center" class="k-login-view">
    <k-login-form />
  </k-view>
  <k-view v-else-if="ready && form === 'code'" align="center" class="k-login-code-view">
    <k-login-code-form />
  </k-view>
</template>

<script>
import LoginForm from "../Forms/Login.vue";
import LoginCodeForm from "../Forms/LoginCode.vue";

export default {
  components: {
    "k-login-form": window.panel.plugins.login || LoginForm,
    "k-login-code-form": LoginCodeForm
  },
  data() {
    return {
      ready: false,
      issue: null
    };
  },
  computed: {
    form() {
      if (this.$store.state.user.pendingEmail) {
        return "code";
      } else {
        return "login";
      }
    }
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

        if (system.pendingChallenge) {
          this.$store.dispatch("user/pending", {
            email: system.pendingEmail,
            challenge: system.pendingChallenge
          });
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
.k-login-fields {
  position: relative;
}

.k-login-toggler {
  position: absolute;
  top: 0;
  right: 0;
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

.k-login-back-button,
.k-login-checkbox {
  display: flex;
  align-items: center;
  flex-grow: 1;
}

.k-login-back-button {
  [dir="ltr"] & {
    margin-left: -1rem;
  }

  [dir="rtl"] & {
    margin-right: -1rem;
  }
}

.k-login-checkbox {
  padding: 0.5rem 0;
  font-size: $text-sm;
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
  font-size: $text-sm;
  border-radius: $rounded-xs;
  box-shadow: $shadow-lg;
  cursor: pointer;
}
</style>
