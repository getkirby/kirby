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
        url: null,
        text: null
      },
      fields: {
        url: {
          label: this.$t("link"),
          type: 'text',
          placeholder: this.$t("url.placeholder"),
          icon: 'url'
        },
        text: {
          label: this.$t("link.text"),
          type: 'text'
        }
      }
    };
  },
  methods: {
    open(url, text) {
      this.value = {
        url: url,
        text: text
      };

      this.$refs.dialog.open();
    },
    cancel() {
      this.$emit("cancel");
    },
    submit() {
      this.$emit("submit", this.value.url, this.value.text);
      this.$refs.dialog.close();
    }
  }
}
</script>
