<script>
import AsyncFormDialog from "@/ui/components/AsyncFormDialog.vue";

export default {
  extends: AsyncFormDialog,
  methods: {
    async load() {
      // load and filter roles
      this.roles = await this.$model.roles.options({ canBe: "created" });

      // get the role of the current user
      const currentRole = this.$user.role && this.$user.role.name ? this.$user.role.name : "admin";

      // don't let non-admins create admins
      if (currentRole !== "admin") {
        this.roles = this.roles.filter(role => {
          return role.value !== "admin";
        });
      }

      // load all translations
      this.languages = await this.$model.translations.options();

      // blank slate
      this.values = {
        email: "",
        language: this.$model.languages.defaultLanguageCode() || "en",
        name: "",
        password: "",
        role: currentRole
      };

      // field setup
      this.fields = {
        name: {
          label: this.$t("name"),
          type: "text",
          trim: true,
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
          type: this.roles.length === 1 ? "hidden" : "radio",
          required: true,
          options: this.roles
        }
      };

      this.submitButton = this.$t("create");
    },
    async submit() {
      return await this.$model.users.create(this.values);
    }
  }
}
</script>
