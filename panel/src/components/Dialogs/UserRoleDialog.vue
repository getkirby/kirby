<template>
  <kirby-dialog
    ref="dialog"
    :button="$t('user.role.change')"
    size="medium"
    theme="positive"
    @submit="$refs.form.submit()"
  >
    <kirby-form
      ref="form"
      :fields="fields"
      v-model="user"
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
          label: this.$t("user.role.select"),
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

      this.$api.user.get(id)
        .then(user => {
          this.$api.role.options().then(roles => {
            this.roles = roles;
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
      this.$api.user
        .changeRole(this.user.id, this.user.role)
        .then(() => {
          this.success({
            message: this.$t("user.role.changed", { role: this.user.role }),
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
