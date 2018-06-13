<template>
  <kirby-error-view v-if="issue">
    {{ issue.message }}
  </kirby-error-view>
  <kirby-view v-else-if="ready" align="center" class="kirby-login-view">
    <form :data-invalid="invalid" class="kirby-login-form" @submit.prevent="login">
      <kirby-fieldset :fields="fields" v-model="user" />
      <div class="kirby-login-buttons">
        <label class="kirby-login-checkbox">
          <kirby-checkbox-input
            :value="user.long"
            label="Keep me logged in"
            @input="user.long = $event"
          />
        </label>
        <kirby-button
          :disabled="!valid"
          class="kirby-login-button"
          icon="check"
          type="submit"
        >
          {{ $t("login") }}
        </kirby-button>
      </div>
    </form>
  </kirby-view>
</template>

<script>
export default {
  data() {
    return {
      ready: false,
      issue: null,
      invalid: false,
      user: {
        email: "",
        password: "",
        long: false
      }
    };
  },
  computed: {
    fields() {
      return {
        email: {
          autofocus: true,
          label: this.$t("user.email"),
          type: "email",
          link: false,
          placeholder: this.$t("user.email.placeholder")
        },
        password: {
          label: this.$t("user.password"),
          type: "password",
          minLength: 8
        }
      };
    },
    valid() {
      return this.user.email.length && this.user.password.length >= 8;
    }
  },
  created() {
    this.$store
      .dispatch("system/load")
      .then(system => {
        if (!system.isReady) {
          this.$router.push("/installation");
        }

        this.ready = true;
        this.$store.dispatch("title", this.$t("login"));
      })
      .catch(error => {
        this.issue = error;
      });
  },
  methods: {
    login() {
      this.invalid = false;

      this.$store
        .dispatch("user/login", this.user)
        .then(() => {
          this.$store.dispatch("notification/success", this.$t("welcome"));
        })
        .catch(() => {
          this.invalid = true;
        });
    }
  }
};
</script>

<style lang="scss">
.kirby-login-form[data-invalid] {
  animation: shake 0.5s linear;
}
.kirby-login-form[data-invalid] .kirby-field label {
  animation: nope 2s linear;
}
.kirby-login-buttons {
  display: flex;
  align-items: center;
  padding: 1.5rem 0;
}
.kirby-login-button {
  padding: 0.5rem 1rem;
  margin-right: -1rem;
  font-weight: 500;
  transition: opacity 0.3s;
}
.kirby-login-button span {
  opacity: 1;
}
.kirby-login-button[disabled] {
  opacity: 0.25;
}
.kirby-login-checkbox {
  display: flex;
  align-items: center;
  padding: 0.5rem 0;
  flex-grow: 1;
  font-size: $font-size-small;
  cursor: pointer;
}
.kirby-login-checkbox .kirby-checkbox-text {
  opacity: 0.75;
  transition: opacity 0.3s;
}
.kirby-login-checkbox:hover span,
.kirby-login-checkbox:focus span {
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
