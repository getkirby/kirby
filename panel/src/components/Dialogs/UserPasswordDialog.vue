<template>
  <kirby-dialog
    ref="dialog"
    :button="$t('change')"
    theme="positive"
    icon="check"
    @submit="$refs.form.submit()"
  >
    <kirby-form
      ref="form"
      :fields="fields"
      v-model="values"
      @submit="submit"
    />
  </kirby-dialog>
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
          label: this.$t("user.password.new"),
          type: "password",
          icon: "key",
          required: true
        },
        passwordConfirmation: {
          label: this.$t("user.password.new.confirm"),
          icon: "key",
          type: "password",
          required: true
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
      // TODO: redundant? already handled by backend
      if (this.values.password.length < 8) {
        this.$store.dispatch(
          "notification/error",
          this.$t("error.user.password.invalid")
        );
        return false;
      }

      if (this.values.password !== this.values.passwordConfirmation) {
        this.$store.dispatch(
          "notification/error",
          this.$t("error.user.password.notSame")
        );
        return false;
      }

      this.$api.users
        .changePassword(this.user.id, this.values.password)
        .then(() => {
          this.success({
            message: this.$t("user.password.changed"),
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
