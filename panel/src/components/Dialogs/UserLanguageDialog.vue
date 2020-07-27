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
        language: "en"
      },
      languages: []
    };
  },
  computed: {
    fields() {
      return {
        language: {
          label: this.$t("language"),
          type: "select",
          icon: "globe",
          options: this.languages,
          required: true,
          empty: false
        }
      };
    }
  },
  methods: {
    async open(id) {

      this.languages = await this.$api.translations.options();

      try {
        this.user = await this.$api.users.get(id, { view: "compact" });
        this.$refs.dialog.open();

      } catch (error) {
        this.$store.dispatch('notification/error', error);
      }
    },
    async submit() {
      try {
        this.user = await this.$api.users.changeLanguage(
          this.user.id,
          this.user.language
        );

        this.success({
          message: ":)",
          event: "user.changeLanguage"
        });
      } catch (error) {
        this.$refs.dialog.error(error.message);
      }
    }
  }
};
</script>
