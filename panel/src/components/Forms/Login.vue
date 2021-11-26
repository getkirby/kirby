<template>
  <form class="k-login-form" @submit.prevent="login">
    <h1 class="sr-only">
      {{ $t("login") }}
    </h1>

    <k-login-alert v-if="issue" @click="issue = null">
      {{ issue }}
    </k-login-alert>

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
      <span v-if="isResetForm === false" class="k-login-checkbox">
        <k-checkbox-input
          :value="user.remember"
          :label="$t('login.remember')"
          @input="user.remember = $event"
        />
      </span>
      <k-button class="k-login-button" icon="check" type="submit">
        {{ $t("login" + (isResetForm ? ".reset" : "")) }}
        <template v-if="isLoading"> … </template>
      </k-button>
    </div>
  </form>
</template>

<script>
export default {
  props: {
    methods: Array
  },
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
      return (
        this.codeMode !== null &&
        this.methods.includes("password") === true &&
        (this.methods.includes("password-reset") === true ||
          this.methods.includes("code") === true)
      );
    },
    codeMode() {
      if (this.methods.includes("password-reset") === true) {
        return "password-reset";
      }
      if (this.methods.includes("code") === true) {
        return "code";
      }
      return null;
    },
    fields() {
      let fields = {
        email: {
          autofocus: true,
          label: this.$t("email"),
          type: "email",
          required: true,
          link: false
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
      }
      if (this.methods[0] === "password") {
        return "email-password";
      }
      return "email";
    },
    isResetForm() {
      return this.codeMode === "password-reset" && this.form === "email";
    },
    toggleText() {
      return this.$t(
        "login.toggleText." + this.codeMode + "." + this.formOpposite(this.form)
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
      this.issue = null;
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
        await this.$api.auth.login(user);

        this.$reload({
          globals: ["$system", "$translation"]
        });
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
