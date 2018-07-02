<template>
  <kirby-dialog
    ref="dialog"
    :button="$t('change')"
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
          label: this.$t("user.email"),
          preselect: true,
          required: true,
          type: "email",
        }
      };
    }
  },
  methods: {
    open(id) {
      this.$api.user.get(id, { select: ["id", "email"] })
        .then(user => {
          this.user = user;
          this.$refs.dialog.open();
        })
        .catch(error => {
          this.$store.dispatch('notification/error', error);
        });
    },
    submit() {
      this.$api.user
        .changeEmail(this.user.id, this.user.email)
        .then(response => {
          this.success({
            message: this.$t("user.email.changed"),
            event: "user.changeEmail",
            route: "/users/" + response.id
          });
        })
        .catch(error => {
          this.$refs.dialog.error(error.message);
        });
    }
  }
};
</script>
