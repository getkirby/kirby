<template>
  <k-form-dialog
    ref="dialog"
    v-model="email"
    :fields="fields"
    :submit-button="$t('confirm')"
    size="medium"
    @close="$emit('close')"
    @submit="submit"
  />
</template>

<script>
export default {
  data() {
    return {
      email: {
        email: null,
        title: null
      }
    };
  },
  computed: {
    fields() {
      return {
        href: {
          label: this.$t("email"),
          type: "email",
          icon: "email"
        },
        title: {
          label: this.$t("title"),
          type: "text",
          icon: "title"
        }
      };
    }
  },
  methods: {
    open(email) {
      this.email = {
        title: null,
        ...email
      };
      this.$refs.dialog.open();
    },
    submit() {
      this.$emit("submit", this.email);
      this.$refs.dialog.close();
    }
  }
};
</script>
