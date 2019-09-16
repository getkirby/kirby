<template>
  <k-dialog
    ref="dialog"
    :button="$t('create')"
    size="medium"
    theme="positive"
    @submit="$refs.form.submit()"
    @close="reset"
  >
    <k-form
      ref="form"
      :fields="fields"
      :novalidate="true"
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
          type: this.roles.length === 1 ? "hidden" : "radio",
          required: true,
          options: this.roles
        }
      };
    }
  },
  methods: {
    create() {
      this.$api.users
        .create(this.user)
        .then(() => {
          this.success({
            message: ":)",
            event: "user.create"
          });
        })
        .catch(error => {
          this.$refs.dialog.error(error.message);
        });
    },
    emptyForm() {
      return {
        name: "",
        email: "",
        password: "",
        language: this.$store.state.system.info.defaultLanguage || "en",
        role: this.$user.role.name
      };
    },
    open() {
      // load and filter roles
      const roles = this.$api.roles.options({ canBe: "created" }).then(roles => {
        this.roles = roles;

        // don't let non-admins create admins
        if (this.$user.role.name !== "admin") {
          this.roles = this.roles.filter(role => {
            return role.value !== "admin";
          });
        }
      }).catch(error => {
        this.$store.dispatch('notification/error', error);
      });

      // load all translations
      const translations = this.$api.translations.options().then(languages => {
        this.languages = languages;
      }).catch (error => {
        this.$store.dispatch('notification/error', error);
      });

      // open dialog when all API requests finished
      Promise.all([roles, translations]).then(() => {
        this.$refs.dialog.open();
      });
    },
    reset() {
      this.user = this.emptyForm();
    }
  }
};
</script>
