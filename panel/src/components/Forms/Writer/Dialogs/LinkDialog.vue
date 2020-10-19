<template>
  <k-form-dialog
    ref="dialog"
    :fields="fields"
    :submit-button="$t('confirm')"
    v-model="link"
    size="medium"
    @submit="submit"
    @close="$emit('close')"
  />
</template>

<script>
export default {
  data() {
    return {
      link: {
        href: null,
        title: null,
        target: false
      }
    };
  },
  computed: {
    fields() {
      return {
        href: {
          label: "URL",
          type: "text",
          icon: "url"
        },
        title: {
          label: "Title",
          type: "text",
          icon: "title"
        },
        target: {
          label: "Open in new window",
          type: "toggle",
          text: ["no", "yes"]
        }
      };
    }
  },
  methods: {
    open(link) {

      console.log(link);

      this.link = {
        title: null,
        target: false,
        ...link
      };
      this.$refs.dialog.open();
    },
    submit() {
      this.$emit("submit", {
        ...this.link,
        target: this.link.target ? "_blank" : null
      });

      this.$refs.dialog.close();
    }
  }
};
</script>
