<template>
  <form class="k-login-form" @submit.prevent="login">
    <h1 class="k-offscreen">{{ $t('login') }}</h1>

    <div v-if="issue" class="k-login-alert" @click="issue = null">
      <span>{{ issue }}</span>
      <k-icon type="alert" />
    </div>

    <k-fieldset :novalidate="true" :fields="fields" v-model="user" />

    <div class="k-login-buttons">
      <span class="k-login-checkbox">
        <k-checkbox-input
          :value="user.remember"
          :label="$t('login.remember')"
          @input="user.remember = $event"
        />
      </span>
      <k-button
        class="k-login-button"
        icon="check"
        type="submit"
      >
        {{ $t("login") }} <template v-if="isLoading">…</template>
      </k-button>
    </div>
  </form>
</template>

<script>
export default {
  data() {
    return {
      isLoading: false,
      issue: "",
      user: {
        email: "",
        password: "",
        remember: false
      }
    };
  },
  computed: {
    fields() {
      return {
        email: {
          autofocus: true,
          label: this.$t("email"),
          type: "email",
          required: true,
          link: false,
        },
        password: {
          label: this.$t("password"),
          type: "password",
          minLength: 8,
          required: true,
          autocomplete: "current-password",
          counter: false
        }
      };
    }
  },
  methods: {
    async login() {
      this.issue     = null;
      this.isLoading = true;
      try {
        await this.$api.auth.login(this.user);
        this.$go("/site");
      } catch (e) {
        this.issue = e.message;
      }
    }
  }
};
</script>
