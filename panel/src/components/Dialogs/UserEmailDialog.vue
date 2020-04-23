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
import DialogMixin from "@/ui/mixins/dialog.js";

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
    open(id) {
      this.$api.users.get(id, { select: ["id", "email"] })
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
        .changeEmail(this.user.id, this.user.email)
        .then(response => {

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
            payload.route = this.$api.users.link(response.id);
          }

          this.success(payload);
        })
        .catch(error => {
          this.$refs.dialog.error(error.message);
        });
    }
  }
};
</script>
