<template>
  <kirby-dialog
    ref="dialog"
    button="Insert"
    @close="cancel"
    @submit="$refs.form.submit()"
  >
    <kirby-form
      ref="form"
      :fields="fields"
      v-model="value"
      @submit="submit"
    />
  </kirby-dialog>
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
          label: 'Email',
          type: 'email'
        },
        text: {
          label: 'Email Link Text',
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
      let tag = "(email: " + this.value.email + ")";

      if (this.value.text.length > 0) {
        tag =
          "(email: " +
          this.value.email +
          " text: " +
          this.value.text +
          ")";
      }

      // reset the form
      this.value = {
        email: null,
        text: null
      };

      this.$emit("submit", tag);

      // close the modal
      this.$refs.dialog.close();
    }
  }
}
</script>
