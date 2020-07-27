<template>
  <k-remove-dialog
    ref="dialog"
    :text="$t('user.delete.confirm', { email: user.email })"
    @submit="submit"
  />
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
    async open(id) {
      try {
        this.user = await this.$api.users.get(id);
        this.$refs.dialog.open();

      } catch (error) {
        this.$store.dispatch('notification/error', error);
      }
    },
    async submit() {
      try {
        await this.$api.users.delete(this.user.id);

        // remove data from cache
        this.$store.dispatch("content/remove", "users/" + this.user.id);

        this.success({
          message: ":)",
          event: "user.delete"
        });
      } catch (error) {
        this.$refs.dialog.error(error.message);
      }
    }
  }
};
</script>
