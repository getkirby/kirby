<template>
  <kirby-dialog
    ref="dialog"
    :button="$t('delete')"
    theme="negative"
    icon="trash"
    @submit="submit"
  >
    <kirby-text v-html="$t('user.delete.confirm', { email: user.email })" />
  </kirby-dialog>
</template>

<script>
import DialogMixin from "@/mixins/dialog.js";

export default {
  mixins: [DialogMixin],
  data() {
    return {
      user: {
        email: null
      }
    };
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
      this.$api.users
        .delete(this.user.id)
        .then(() => {
          this.success({
            message: this.$t("user.deleted"),
            event: "user.delete"
          });

          if (this.$route.name === "User") {
            this.$router.push("/users");
          }
        })
        .catch(error => {
          this.$refs.dialog.error(error.message);
        });
    }
  }
};
</script>
