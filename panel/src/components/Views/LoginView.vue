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
          this.$router.push("/installation");
        }

        if (system.user && system.user.id) {
          this.$router.push("/");
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
  .k-login-form[data-invalid] {
    animation: shake 0.5s linear;
  }

  .k-login-form[data-invalid] .k-field label {
    animation: nope 2s linear;
  }

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

  @keyframes nope {
    0% {
      color: $color-negative;
    }

    100% {
      color: $color-dark;
    }
  }

  @keyframes shake {

    8%,
    41% {
      transform: translateX(-10px);
    }

    25%,
    58% {
      transform: translateX(10px);
    }

    75% {
      transform: translateX(-5px);
    }

    92% {
      transform: translateX(5px);
    }

    0%,
    100% {
      transform: translateX(0);
    }
  }
</style>
