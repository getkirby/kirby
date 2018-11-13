<template>
  <k-dialog
    ref="dialog"
    :button="$t('insert')"
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
  computed: {
    kirbytext() {
      return this.$store.state.system.info.kirbytext;
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
        return `(email: ${this.value.email} text: ${this.value.text})`;
      } else {
        return `(email: ${this.value.email})`;
      }
    },
    createMarkdown() {
      if (this.value.text.length > 0) {
        return `[${this.value.text}](mailto:${this.value.email})`;
      } else {
        return `<${this.value.email}>`;
      }
    },
    submit() {
      // insert the link
      this.$emit("submit", this.kirbytext ? this.createKirbytext() : this.createMarkdown());

      // reset the form
      this.value = {
        email: null,
        text: null
      };

      // close the modal
      this.$refs.dialog.close();
    }
  }
}
</script>
