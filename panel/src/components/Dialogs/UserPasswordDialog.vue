<template>
  <k-form-dialog
    ref="dialog"
    v-model="values"
    :fields="fields"
    :submit-button="$t('change')"
    @submit="submit"
  />
</template>

<script>
import DialogMixin from "@/mixins/dialog.js";

export default {
  mixins: [DialogMixin],
  data() {
    return {
      user: null,
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
          label: this.$t("user.changePassword.new"),
          type: "password",
          icon: "key",
        },
        passwordConfirmation: {
          label: this.$t("user.changePassword.new.confirm"),
          icon: "key",
          type: "password"
        }
      };
    }
  },
  methods: {
    async open(id) {
      try {
        this.user = await this.$api.users.get(id);
        this.$refs.dialog.open();

      } catch (error) {
        this.$store.dispatch('notification/error', error);
      }
    },
    async submit() {
      if (!this.values.password || this.values.password.length < 8) {
        this.$refs.dialog.error(this.$t("error.user.password.invalid"));
        return false;
      }

      if (this.values.password !== this.values.passwordConfirmation) {
        this.$refs.dialog.error(this.$t("error.user.password.notSame"));
        return false;
      }

      try {
        await this.$api.users.changePassword(
          this.user.id,
          this.values.password
        );

        this.success({
          message: ":)",
          event: "user.changePassword"
        });

      } catch (error) {
        this.$refs.dialog.error(error.message);
      }
    }
  }
};
</script>
