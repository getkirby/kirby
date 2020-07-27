<template>
  <k-form-dialog
    ref="dialog"
    v-model="user"
    :fields="fields"
    :submit-button="$t('user.changeRole')"
    @submit="submit"
  />
</template>

<script>
import DialogMixin from "@/mixins/dialog.js";

export default {
  mixins: [DialogMixin],
  data() {
    return {
      roles: [],
      user: {
        id: null,
        role: "visitor"
      }
    };
  },
  computed: {
    fields() {
      return {
        role: {
          label: this.$t("user.changeRole.select"),
          type: "radio",
          required: true,
          options: this.roles
        }
      };
    }
  },
  methods: {
    async open(id) {
      this.id = id;

      try {
        this.user      = await this.$api.users.get(id);
        this.user.role = this.user.role.name;
        this.roles     = await this.$api.users.roles(id);

        // don't let non-admins promote anyone to admin
        if (this.$user.role.name !== "admin") {
          this.roles = this.roles.filter(role => role.value !== "admin");
        }

        this.$refs.dialog.open();

      } catch (error) {
        this.$store.dispatch('notification/error', error);
      }
    },
    async submit() {
      try {
        await this.$api.users.changeRole(this.user.id, this.user.role);

        this.success({
          message: ":)",
          event: "user.changeRole"
        });
      } catch (error) {
        this.$refs.dialog.error(error.message);
      }
    }
  }
};
</script>
