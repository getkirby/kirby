<template>
  <form class="k-login-form k-login-code-form" @submit.prevent="login">
    <h1 class="k-offscreen">
      {{ $t('login') }}
    </h1>

    <div v-if="issue" class="k-login-alert" @click="issue = null">
      <span>{{ issue }}</span>
      <k-icon type="alert" />
    </div>

    <k-user-info :user="$store.state.user.pendingEmail" />

    <k-text-field
      v-model="code"
      :autofocus="true"
      :counter="false"
      :help="$t('login.code.text.' + $store.state.user.pendingChallenge)"
      :label="$t('login.code.label.' + mode)"
      :novalidate="true"
      :placeholder="$t('login.code.placeholder.' + $store.state.user.pendingChallenge)"
      :required="true"
      autocomplete="one-time-code"
      icon="unlock"
      name="code"
    />

    <div class="k-login-buttons">
      <k-button
        class="k-login-button k-login-back-button"
        icon="angle-left"
        @click="back"
      >
        {{ $t("back") }} <template v-if="isLoadingBack">
          …
        </template>
      </k-button>

      <k-button
        class="k-login-button"
        icon="check"
        type="submit"
      >
        {{ $t("login" + (mode === "password-reset" ? ".reset" : "")) }}
        <template v-if="isLoadingLogin">
          …
        </template>
      </k-button>
    </div>
  </form>
</template>

<script>
export default {
  data() {
    return {
      code: "",
      isLoadingBack: false,
      isLoadingLogin: false,
      issue: ""
    };
  },
  computed: {
    mode() {
      if (this.$store.state.system.info.loginMethods.includes("password-reset") === true) {
        return "password-reset";
      } else {
        return "login";
      }
    }
  },
  methods: {
    async back() {
      this.isLoadingBack = true;
      await this.$store.dispatch("user/logout");
      this.isLoadingBack = false;
    },
    async login() {
      this.issue          = null;
      this.isLoadingLogin = true;

      try {
        const result = await this.$api.auth.verifyCode(this.code);

        if (this.mode === "password-reset") {
          this.$store.dispatch("user/visit", "/reset-password");
        }

        this.$store.dispatch("user/login", result.user);
        await this.$store.dispatch("system/load", true);

        this.$store.dispatch("notification/success", this.$t("welcome"));
      } catch (error) {
        this.issue = error.message;
      } finally {
        this.isLoadingLogin = false;
      }
    }
  }
};
</script>

<style>
.k-login-code-form .k-user-info {
  height: 38px;
  margin-bottom: 2.25rem;
  padding: .5rem;
  background: var(--color-white);
  border-radius: var(--rounded-xs);
  box-shadow: var(--shadow);
}
</style>
