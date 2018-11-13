<template>
  <k-dialog
    ref="dialog"
    :button="$t('create')"
    size="medium"
    theme="positive"
    @submit="$refs.form.submit()"
  >
    <k-form
      ref="form"
      :fields="fields"
      v-model="user"
      @submit="create"
    />
  </k-dialog>
</template>

<script>
import DialogMixin from "@/mixins/dialog.js";

export default {
  mixins: [DialogMixin],
  data() {
    return {
      user: {
        email: "",
        password: "",
        language: "en",
        // TODO: change to config default user role
        role: "admin"
      },
      roles: []
    };
  },
  computed: {
    fields() {
      return {
        email: {
          label: this.$t("email"),
          type: "email",
          icon: "email",
          id: "new-user-email",
          link: false,
          required: true
        },
        password: {
          label: this.$t("password"),
          type: "password",
          icon: "key",
          id: "new-user-password",
          required: true
        },
        role: {
          label: this.$t("role"),
          type: "radio",
          required: true,
          options: this.roles
        }
      };
    }
  },
  methods: {
    open() {
      this.$api.roles.options()
        .then(roles => {
          this.roles = roles;
          this.$refs.dialog.open();
        })
        .catch(error => {
          this.$store.dispatch('notification/error', error);
        });
    },
    create() {
      this.$api.users
        .create(this.user)
        .then(() => {
          this.user = {
            email: "",
            password: "",
            language: "en",
            // TODO: change to config default user role
            role: "admin"
          };

          this.success({
            message: ":)",
            event: "user.create"
          });
        })
        .catch(error => {
          this.$refs.dialog.error(error.message);
        });
    }
  }
};
</script>
