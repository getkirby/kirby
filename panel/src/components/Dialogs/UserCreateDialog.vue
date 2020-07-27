<template>
  <k-form-dialog
    ref="dialog"
    v-model="user"
    :fields="fields"
    :submit-button="$t('create')"
    @submit="create"
    @close="reset"
  />
</template>

<script>
import DialogMixin from "@/mixins/dialog.js";

export default {
  mixins: [DialogMixin],
  data() {
    return {
      user: this.emptyForm(),
      languages: [],
      roles: []
    };
  },
  computed: {
    fields() {
      return {
        name: {
          label: this.$t("name"),
          type: "text",
          icon: "user",
        },
        email: {
          label: this.$t("email"),
          type: "email",
          icon: "email",
          link: false,
          required: true
        },
        password: {
          label: this.$t("password"),
          type: "password",
          icon: "key",
        },
        language: {
          label: this.$t("language"),
          type: "select",
          icon: "globe",
          options: this.languages,
          required: true,
          empty: false
        },
        role: {
          label: this.$t("role"),
          type: this.roles.length <= 1 ? "hidden" : "radio",
          required: true,
          options: this.roles
        }
      };
    }
  },
  methods: {
    async create() {
      try {
        await this.$api.users.create(this.user);
        this.success({
          message: ":)",
          event: "user.create"
        });

      } catch (error) {
        this.$refs.dialog.error(error.message);
      }
    },
    emptyForm() {
      return {
        name: "",
        email: "",
        password: "",
        language: this.$config.translation || "en",
        role: this.$user.role.name
      };
    },
    async open() {

      // load and filter roles
      try {
        this.roles = await this.$api.roles.options({ canBe: "created" });

        // don't let non-admins create admins
        if (this.$user.role.name !== "admin") {
          this.roles = this.roles.filter(role => role.value !== "admin");
        }

      } catch (error) {
        this.$store.dispatch('notification/error', error);
      }

      // load all translations
      try {
        this.languages = await this.$api.translations.options();

      } catch (error) {
        this.$store.dispatch('notification/error', error);
      }

      // open dialog when all API requests finished
      this.$refs.dialog.open();
    },
    reset() {
      this.user = this.emptyForm();
    }
  }
};
</script>
