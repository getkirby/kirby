<template>
  <k-form
    ref="form"
    :autofocus="!authenticating && !processing"
    :fields="fields"
    v-model="values"
    @submit="authenticate"
  >
    <template v-slot:footer>
      <footer class="pt-6 flex justify-between">
        <k-toggle-input
          class="text-sm"
          :text="$t('login.remember')"
          v-model="values.remember"
        />
        <k-button
          :loading="processing || authenticating"
          :text="$t('login')"
          class="k-login-button p-3"
          icon="check"
          type="submit"
          theme="positive"
        />
      </footer>
    </template>
  </k-form>
</template>

<script>
export default {
  props: {
    processing: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      authenticating: false,
      values: {
        email: null,
        password: null,
        remember: false,
      }
    };
  },
  computed: {
    fields() {
      return {
        email: {
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
  methods: {
    async authenticate(values) {
      this.authenticating = true;

      try {
        // authenticate user
        const user = await this.$api.auth.login(values);
        this.$emit("login", user);

      } catch (error) {
        this.$store.dispatch("notification/error", {
          ...error,
          permanent: false
        });

      } finally {
        this.authenticating = false;
      }
    }
  }
}
</script>
