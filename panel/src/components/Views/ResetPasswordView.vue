<template>
  <k-view class="k-password-reset-view" align="center">
    <k-form
      v-model="values"
      :fields="fields"
      :submit-button="$t('change')"
      @submit="submit"
    >
      <template slot="header">
        <h1 class="k-offscreen">
          {{ $t('view.resetPassword') }}
        </h1>

        <div v-if="issue" class="k-login-alert" @click="issue = null">
          <span>{{ issue }}</span>
          <k-icon type="alert" />
        </div>

        <k-user-info :user="$user" />
      </template>

      <div slot="footer" class="k-login-buttons">
        <k-button
          class="k-login-button"
          icon="check"
          type="submit"
        >
          {{ $t("change") }} <template v-if="isLoading">
            …
          </template>
        </k-button>
      </div>
    </k-form>
  </k-view>
</template>

<script>
export default {
  data() {
    return {
      isLoading: false,
      issue: "",
      values: {
        password: null,
        passwordConfirmation: null
      }
    };
  },
  computed: {
    fields() {
      return {
        password: {
          autofocus: true,
          label: this.$t("user.changePassword.new"),
          icon: "key",
          type: "password"
        },
        passwordConfirmation: {
          label: this.$t("user.changePassword.new.confirm"),
          icon: "key",
          type: "password"
        }
      };
    }
  },
  mounted() {
    this.$store.dispatch("title", this.$t("view.resetPassword"));
  },
  methods: {
    async submit() {
      if (!this.values.password || this.values.password.length < 8) {
        this.issue = this.$t("error.user.password.invalid");
        return false;
      }

      if (this.values.password !== this.values.passwordConfirmation) {
        this.issue = this.$t("error.user.password.notSame");
        return false;
      }

      this.isLoading = true;

      try {
        await this.$api.users.changePassword(
          this.$user.id,
          this.values.password
        );

        this.$store.dispatch("notification/success", ":)");
        this.$go("/");
      } catch (error) {
        this.issue = error.message;
      } finally {
        this.isLoading = false;
      }
    }
  }
};
</script>

<style>
.k-password-reset-view .k-user-info {
  height: 38px;
  margin-bottom: 2.25rem;
  padding: .5rem;
  background: var(--color-white);
  border-radius: var(--rounded-xs);
  box-shadow: var(--shadow);
}
</style>
