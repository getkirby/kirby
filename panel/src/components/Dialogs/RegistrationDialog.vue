<template>
  <k-form-dialog
    ref="dialog"
    v-model="registration"
    :fields="fields"
    :submit-button="$t('license.register')"
    @submit="submit"
  />
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
    async submit() {
      try {
        await this.$api.system.register(this.registration);

        this.success({
          message: this.$t("license.register.success")
        });
      } catch (error) {
        this.$refs.dialog.error(error.message);
      }
    }
  }
};
</script>
