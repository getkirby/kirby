<template>
  <form :data-invalid="invalid" class="k-login-form" @submit.prevent="login">
    <h1 class="k-offscreen">{{ $t('login') }}</h1>
    
    <div class="k-login-tooltip" v-if="issue">
      {{ issue }}
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
      invalid: false,
      isLoading: false,
      issue: null,
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
          link: false
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
    login() {
      this.invalid   = false;
      this.issue     = null;
      this.isLoading = true;

      this.$store
        .dispatch("user/login", this.user)
        .then(() => {
          this.$store.dispatch("system/load", true).then(() => {
            this.$store.dispatch("notification/success", this.$t("welcome"));
            this.isLoading = false;
          });
        })
        .catch(response => {
          this.invalid   = true;
          this.issue     = response.message;
          this.isLoading = false;
        });
    }
  }
};
</script>

<style lang="scss">
.k-login-tooltip {
  position: relative;
  padding: .5rem .25rem;
  margin-bottom: 1rem;
  background-color: $color-negative;
  color: $color-white;
  font-size: $font-size-small;
  text-align: center;
}
</style>

