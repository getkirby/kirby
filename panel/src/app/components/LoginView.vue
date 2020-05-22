<template>
  <k-view align="center">
    <k-form
      ref="form"
      :fields="fields"
      v-model="credentials"
      @submit="onLogin"
    >
      <template v-slot:header>
        <k-notification
          :message="error"
          type="error"
          class="mb-8 rounded-xs shadow-md"
          @close="onResetError"
        />
      </template>

      <template v-slot:footer>
        <footer class="pt-6 flex justify-between">
          <k-toggle-input
            class="text-sm"
            text="Remember me"
            v-model="remember"
          />
          <k-button
            class="k-login-button p-3"
            icon="check"
            type="submit"
            theme="positive">
            Login
          </k-button>
        </footer>
      </template>
    </k-form>
  </k-view>
</template>

<script>
export default {
  data() {
    return {
      credentials: {
        email: null,
        password: null,
      },
      remember: false,
      error: null,
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
      }
    }
  },
}
</script>

<style>
.k-login-button {
  margin-right: -.75rem;
}
</style>
