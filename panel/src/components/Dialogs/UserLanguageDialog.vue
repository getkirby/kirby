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
import DialogMixin from "@/ui/mixins/dialog.js";

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
  created() {
    this.$api.translations.options().then(languages => {
      this.languages = languages;
    });
  },
  methods: {
    open(id) {
      this.$api.users.get(id, { view: "compact" })
        .then(user => {
          this.user = user;
          this.$refs.dialog.open();
        })
        .catch(error => {
          this.$store.dispatch('notification/error', error);
        });
    },
    submit() {
      this.$api.users
        .changeLanguage(this.user.id, this.user.language)
        .then(user => {
          this.user = user;

          // If current panel user, update store to switch language
          if (this.$user.id === this.user.id) {
            this.$store.dispatch("user/language", this.user.language);
          }

          this.success({
            message: ":)",
            event: "user.changeLanguage"
          });
        })
        .catch(error => {
          this.$refs.dialog.error(error.message);
        });
    }
  }
};
</script>
