<template>
  <k-form-dialog
    ref="dialog"
    v-model="user"
    :fields="fields"
    :submit-button="$t('rename')"
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
    async open(id) {
      try {
        this.user = await this.$api.users.get(id, { select: ["id", "name"] });
        this.$refs.dialog.open();

      } catch (error) {
        this.$store.dispatch('notification/error', error);
      }
    },
    async submit() {
      this.user.name = this.user.name.trim();

      try {
        await this.$api.users.changeName(this.user.id, this.user.name);

        // If current panel user, update store
        if (this.$user.id === this.user.id) {
          this.$store.dispatch("user/name", this.user.name);
        }

        this.success({
          message: ":)",
          event: "user.changeName"
        });

      } catch (error) {
        this.$refs.dialog.error(error.message);
      }
    }
  }
};
</script>
