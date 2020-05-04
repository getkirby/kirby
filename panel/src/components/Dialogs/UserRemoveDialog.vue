<template>
  <k-remove-dialog
    ref="dialog"
    :text="$t('user.delete.confirm', { email: user.email })"
    @submit="submit"
  />
</template>

<script>
import DialogMixin from "@/ui/mixins/dialog.js";

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
    async open(id) {
      try {
        this.user = this.$api.users.get(id);
        this.$refs.dialog.open();

      } catch (error) {
        this.$store.dispatch('notification/error', error);
      }
    },
    async submit() {
      try {
        await this.$api.users.delete(this.user.id);

        // remove data from cache
        await this.$store.dispatch("content/remove", "users/" + this.user.id);

        const payload = {
          message: ":)",
          event: "user.delete"
        };

        if (this.$route.name === "User") {
          payload.route = "/users";
        }

        this.success(payload);

      } catch (error) {
        this.$refs.dialog.error(error.message);
      }
    }
  }
};
</script>
