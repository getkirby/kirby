<template>
  <k-dialog
    ref="dialog"
    :button="$t('license.register')"
    size="medium"
    @submit="submit"
  >
    <k-form
      :fields="fields"
      :novalidate="true"
      v-model="registration"
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
      registration: {
        license: null,
        email: null
      }
    };
  },
  computed: {
    fields() {
      return {
        license: {
          label: this.$t("license.register.label"),
          type: "text",
          required: true,
          counter: false,
          placeholder: "K3-",
          help: this.$t("license.register.help")
        },
        email: {
          label: this.$t("email"),
          type: "email",
          required: true,
          counter: false
        }
      };
    }
  },
  methods: {
    submit() {
      this.$api.system
        .register(this.registration)
        .then(() => {
          this.$store.dispatch("system/register", this.registration.license);
          this.success({
            message: this.$t("license.register.success")
          });
        })
        .catch(error => {
          this.$refs.dialog.error(error.message);
        });
    }
  }
};
</script>
