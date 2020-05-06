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
import DialogMixin from "@/ui/mixins/dialog.js";

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
      try {
        this.id = id;
        this.user = await this.$api.users.get(id);
        this.roles = await this.$model.roles.options({ canBe: "changed" });

        // don't let non-admins promote anyone to admin
        if (this.$user.role.name !== "admin") {
          this.roles = this.roles.filter(role => {
            return role.value !== "admin";
          });
        }

        this.user.role = this.user.role.name;
        this.$refs.dialog.open();

      } catch (error) {
        this.$store.dispatch('notification/error', error);
      }
    },
    async submit() {
      try {
        await this.$api.users.changeRole(this.user.id, this.user.role);

        // if current panel user, update store
        if (this.$user.id === this.user.id) {
          await this.$store.dispatch("user/load");
        }

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
