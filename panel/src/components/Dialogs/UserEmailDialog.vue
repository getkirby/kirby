<template>
  <k-form-dialog
    ref="dialog"
    v-model="user"
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
      user: {
        id: null,
        email: null
      }
    };
  },
  computed: {
    fields() {
      return {
        email: {
          label: this.$t("email"),
          preselect: true,
          required: true,
          type: "email",
        }
      };
    }
  },
  methods: {
    async open(id) {
      try {
        this.user = await this.$api.users.get(id, {
          select: ["id", "email"]
        });
        this.$refs.dialog.open();

      } catch (error) {
        this.$store.dispatch('notification/error', error);
      }
    },
    async submit() {
      try {
        const user = await this.$api.users.changeEmail(this.user.id, this.user.email);

        // remove changes for the old user
        this.$store.dispatch("content/revert", "users/" + this.user.id);

        // If current panel user, update store
        if (this.$user.id === this.user.id) {
          this.$store.dispatch("user/email", this.user.email);
        }

        let payload = {
          message: ":)",
          event: "user.changeEmail",
        };

        if (this.$route.name === "User") {
          payload.route = this.$api.users.link(user.id);
        }

        this.success(payload);

      } catch (error) {
        this.$refs.dialog.error(error.message);
      }
    }
  }
};
</script>
