<template>
  <k-dialog
    ref="dialog"
    :submit-button="$t('insert')"
    @close="cancel"
    @submit="$refs.form.submit()"
  >
    <k-form
      ref="form"
      :fields="fields"
      v-model="value"
      @submit="submit"
    />
  </k-dialog>
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
  computed: {
    kirbytext() {
      return this.$config.kirbytext;
    }
  },
  methods: {
    open(input, selection) {
      this.value.text = selection;
      this.$refs.dialog.open();
    },
    cancel() {
      this.$emit("cancel");
    },
    createKirbytext() {
      if (this.value.text.length > 0) {
        return `(link: ${this.value.url} text: ${this.value.text})`;
      } else {
        return `(link: ${this.value.url})`;
      }
    },
    createMarkdown() {
      if (this.value.text.length > 0) {
        return `[${this.value.text}](${this.value.url})`;
      } else {
        return `<${this.value.url}>`;
      }
    },
    submit() {

      // insert the link
      this.$emit("submit", this.kirbytext ? this.createKirbytext() : this.createMarkdown());

      // reset the form
      this.value = {
        url: null,
        text: null
      };

      // close the modal
      this.$refs.dialog.close();
    }
  }
}
</script>
