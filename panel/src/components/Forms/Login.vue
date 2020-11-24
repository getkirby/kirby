<template>
  <form class="k-login-form" @submit.prevent="login">
    <h1 class="k-offscreen">
      {{ $t('login') }}
    </h1>

    <div v-if="issue" class="k-login-alert" @click="issue = null">
      <span>{{ issue }}</span>
      <k-icon type="alert" />
    </div>

    <div class="k-login-fields">
      <button
        v-if="canToggle === true"
        class="k-login-toggler"
        type="button"
        @click="toggleForm"
      >
        {{ toggleText }}
      </button>

      <k-fieldset
        ref="fieldset"
        v-model="user"
        :novalidate="true"
        :fields="fields"
      />
    </div>

    <div class="k-login-buttons">
      <span
        v-if="isResetForm === false"
        class="k-login-checkbox"
      >
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
        {{ $t("login" + (isResetForm ? ".reset" : "")) }}
        <template v-if="isLoading">
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
      currentForm: null,
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
    canToggle() {
      let loginMethods = this.$store.state.system.info.loginMethods;

      return (
        this.codeMode !== null &&
        loginMethods.includes("password") === true &&
        (
          loginMethods.includes("password-reset") === true ||
          loginMethods.includes("code") === true
        )
      );
    },
    codeMode() {
      let loginMethods = this.$store.state.system.info.loginMethods;

      if (loginMethods.includes("password-reset") === true) {
        return "password-reset";
      } else if (loginMethods.includes("code") === true) {
        return "code";
      } else {
        return null;
      }
    },
    fields() {
      let fields = {
        email: {
          autofocus: true,
          label: this.$t("email"),
          type: "email",
          required: true,
          link: false,
        }
      };

      if (this.form === "email-password") {
        fields.password = {
          label: this.$t("password"),
          type: "password",
          minLength: 8,
          required: true,
          autocomplete: "current-password",
          counter: false
        };
      }

      return fields;
    },
    form() {
      if (this.currentForm) {
        return this.currentForm;
      } else if (this.$store.state.system.info.loginMethods[0] === "password") {
        return "email-password";
      } else {
        return "email";
      }
    },
    isResetForm() {
      return (
        this.codeMode === "password-reset" &&
        this.form === "email"
      );
    },
    toggleText() {
      return this.$t(
        "login.toggleText." +
        this.codeMode + "." +
        this.formOpposite(this.form)
      );
    }
  },
  methods: {
    formOpposite(input) {
      if (input === "email-password") {
        return "email";
      } else {
        return "email-password";
      }
    },
    async login() {
      this.issue     = null;
      this.isLoading = true;

      // clear field data that is not needed for login
      let user = Object.assign({}, this.user);

      if (this.currentForm === "email") {
        user.password = null;
      }

      if (this.isResetForm === true) {
        user.remember = false;
      }

      try {
        const result = await this.$api.auth.login(user);

        if (result.challenge) {
          this.$store.dispatch("user/pending", {
            email: user.email,
            challenge: result.challenge
          });
        } else {
          this.$store.dispatch("user/login", result.user);
          await this.$store.dispatch("system/load", true);

          this.$store.dispatch("notification/success", this.$t("welcome"));
        }
      } catch (error) {
        this.issue = error.message;
      } finally {
        this.isLoading = false;
      }
    },
    toggleForm() {
      this.currentForm = this.formOpposite(this.form);
      this.$refs.fieldset.focus("email");
    }
  }
};
</script>
