<template>
  <k-dialog
    ref="dialog"
    button="Insert"
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
          label: 'Link',
          type: 'text',
          icon: 'url'
        },
        text: {
          label: 'Link Text',
          type: 'text'
        }
      }
    };
  },
  methods: {
    open(input, selection) {
      this.value.text = selection;
      this.$refs.dialog.open();
    },
    cancel() {
      this.$emit("cancel");
    },
    submit() {
      let tag = "(link: " + this.value.url + ")";

      if (this.value.text.length > 0) {
        tag =
          "(link: " +
          this.value.url +
          " text: " +
          this.value.text +
          ")";
      }

      // reset the form
      this.value = {
        url: null,
        text: null
      };

      // insert the link
      this.$emit("submit", tag);

      // close the modal
      this.$refs.dialog.close();
    }
  }
}
</script>
