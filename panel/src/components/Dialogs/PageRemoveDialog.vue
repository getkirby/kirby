<template>
  <k-dialog
    ref="dialog"
    :button="$t('delete')"
    theme="negative"
    icon="trash"
    @submit="submit"
  >
    <k-text v-html="$t('page.delete.confirm', { title: page.title })" />
  </k-dialog>
</template>

<script>
import DialogMixin from "@/mixins/dialog.js";

export default {
  mixins: [DialogMixin],
  data() {
    return {
      page: {
        title: null
      }
    };
  },
  methods: {
    open(id) {
      this.$api.pages.get(id)
        .then(page => {
          this.page = page;
          this.$refs.dialog.open();
        })
        .catch(error => {
          this.$store.dispatch('notification/error', error);
        });
    },
    submit() {

      this.$api.pages
        .delete(this.page.id)
        .then(() => {
          // remove data from cache
          this.$store.dispatch("form/reset", this.$route.path);

          const payload = {
            message: this.$t("page.deleted"),
            event: "page.delete"
          };

          // if in PageView, redirect
          if (
            this.$route.params.path &&
            this.page.id === this.$route.params.path.replace(/\+/g, "/")
          ) {
            if (this.page.parent) {
              payload.route = "/pages/" + this.page.parent.id;
            } else {
              payload.route = "/pages";
            }
          }

          this.success(payload);
        })
        .catch(error => {
          this.$refs.dialog.error(error.message);
        });
    }
  }
};
</script>
