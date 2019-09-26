<template>
  <k-dialog
    ref="dialog"
    :button="$t('rename')"
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
      user: {
        id: null,
        name: null
      }
    };
  },
  computed: {
    fields() {
      return {
        name: {
          label: this.$t("name"),
          type: "text",
          icon: "user",
          preselect: true
        }
      };
    }
  },
  methods: {
    open(id) {
      this.$api.users.get(id, { select: ["id", "name"] })
        .then(user => {
          this.user = user;
          this.$refs.dialog.open();
        })
        .catch(error => {
          this.$store.dispatch('notification/error', error);
        });
    },
    submit() {
      this.user.name = this.user.name.trim();

      this.$api.users
        .changeName(this.user.id, this.user.name)
        .then(() => {
          // If current panel user, update store
          if (this.$user.id === this.user.id) {
            this.$store.dispatch("user/name", this.user.name);
          }

          this.success({
            message: ":)",
            event: "user.changeName"
          });
        })
        .catch(error => {
          this.$refs.dialog.error(error.message);
        });
    }
  }
};
</script>
