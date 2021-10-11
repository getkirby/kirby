<template>
  <k-form-dialog
    ref="dialog"
    v-model="link"
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
          label: this.$t("url"),
          type: "text",
          icon: "url"
        },
        title: {
          label: this.$t("title"),
          type: "text",
          icon: "title"
        },
        target: {
          label: this.$t("open.newWindow"),
          type: "toggle",
          text: [this.$t("no"), this.$t("yes")]
        }
      };
    }
  },
  methods: {
    open(link) {
      this.link = {
        title: null,
        target: false,
        ...link
      };

      this.link.target = Boolean(this.link.target);
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
