<template>
  <k-dialog ref="dialog" size="medium" :button="$t('license.register')" @submit="submit">
    <k-form :fields="fields" v-model="registration" @submit="submit" />
  </k-dialog>
</template>

<script>
import DialogMixin from "@/mixins/dialog.js";

export default {
  mixins: [DialogMixin],
  data() {
    return {
      registration: {
        license: null
      },
    };
  },
  computed: {
    fields() {
      return {
        license: {
          label: this.$t("license.register.label"),
          type: 'text',
          required: true,
          counter: false,
          placeholder: 'K3-',
          help: this.$t("license.register.help")
        }
      }
    },
  },
  methods: {
    submit() {
      this.$api.system
        .register(this.registration.license)
        .then(() => {
          this.$store.dispatch("system/register", this.registration.license);
          this.success({
            message: this.$t("license.register.success"),
          });
        })
        .catch(error => {
          this.$refs.dialog.error(error.message);
        });
    }
  }
};
</script>
