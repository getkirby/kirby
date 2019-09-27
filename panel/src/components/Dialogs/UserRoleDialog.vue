<template>
  <k-dialog
    ref="dialog"
    :button="$t('user.changeRole')"
    size="medium"
    theme="positive"
    @submit="$refs.form.submit()"
  >
    <k-form
      ref="form"
      :fields="fields"
      v-model="user"
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
    open(id) {
      this.id = id;

      this.$api.users.get(id)
        .then(user => {
          this.$api.roles.options({ canBe: "changed" }).then(roles => {
            this.roles = roles;

            // don't let non-admins promote anyone to admin
            if (this.$user.role.name !== "admin") {
              this.roles = this.roles.filter(role => {
                return role.value !== "admin";
              });
            }

            this.user = user;
            this.user.role = this.user.role.name;
            this.$refs.dialog.open();
          });
        })
        .catch(error => {
          this.$store.dispatch('notification/error', error);
        });
    },
    submit() {
      this.$api.users
        .changeRole(this.user.id, this.user.role)
        .then(() => {
          // If current panel user, update store
          if (this.$user.id === this.user.id) {
            this.$store.dispatch("user/load");
          }

          this.success({
            message: ":)",
            event: "user.changeRole"
          });
        })
        .catch(error => {
          this.$refs.dialog.error(error.message);
        });
    }
  }
};
</script>
