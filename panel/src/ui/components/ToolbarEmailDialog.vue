<template>
  <k-form-dialog
    ref="dialog"
    v-model="value"
    :fields="fields"
    :submit-button="$t('insert')"
    v-on="{
      ...$listeners,
      submit: submit
    }"
  />
</template>

<script>
export default {
  data() {
    return {
      value: {
        email: null,
        text: null
      },
      fields: {
        email: {
          label: this.$t("email"),
          type: 'email'
        },
        text: {
          label: this.$t("link.text"),
          type: 'text'
        }
      }
    };
  },
  methods: {
    open(email, text) {
      this.value = {
        email: email,
        text: text
      };

      this.$refs.dialog.open();
    },
    submit() {
      // insert the link
      this.$emit("submit", this.value.email, this.value.text);

      // close the modal
      this.$refs.dialog.close();
    }
  }
}
</script>
