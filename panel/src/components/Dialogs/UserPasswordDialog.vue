<template>
  <k-dialog
    ref="dialog"
    :button="$t('change')"
    theme="positive"
    icon="check"
    @submit="$refs.form.submit()"
  >
    <k-form
      ref="form"
      :fields="fields"
      v-model="values"
      @submit="submit"
    />
  </k-dialog>
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
    open(id) {
      this.$api.users.get(id)
        .then(user => {
          this.user = user;
          this.$refs.dialog.open();
        })
        .catch(error => {
          this.$store.dispatch('notification/error', error);
        });
    },
    submit() {
      if (!this.values.password || this.values.password.length < 8) {
        this.$refs.dialog.error(this.$t("error.user.password.invalid"));
        return false;
      }

      if (this.values.password !== this.values.passwordConfirmation) {
        this.$refs.dialog.error(this.$t("error.user.password.notSame"));
        return false;
      }

      this.$api.users
        .changePassword(this.user.id, this.values.password)
        .then(() => {
          this.success({
            message: ":)",
            event: "user.changePassword"
          });
        })
        .catch(error => {
          this.$refs.dialog.error(error.message);
        });
    }
  }
};
</script>
