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
        await this.$api.users.changeEmail(this.user.id, this.user.email);

        // remove changes for the old user
        this.$store.dispatch("content/revert", "users/" + this.user.id);

        let payload = {
          message: ":)",
          event: "user.changeEmail",
        };

        this.success(payload);

      } catch (error) {
        this.$refs.dialog.error(error.message);
      }
    }
  }
};
</script>
