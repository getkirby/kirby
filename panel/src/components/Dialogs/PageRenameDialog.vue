<template>
  <k-form-dialog
    ref="dialog"
    v-model="page"
    :fields="fields"
    :submit-button="$t('rename')"
    @submit="submit"
  />
</template>

<script>
import DialogMixin from "@/mixins/dialog.js";

export default {
  mixins: [DialogMixin],
  data() {
    return {
      page: {
        id: null,
        title: null
      }
    };
  },
  computed: {
    fields() {
      return {
        title: {
          label: this.$t("title"),
          type: "text",
          required: true,
          icon: "title",
          preselect: true
        }
      };
    }
  },
  methods: {
    async open(id) {
      try {
        this.page = await this.$api.pages.get(id, { select: ["id", "title"] });
        this.$refs.dialog.open();

      } catch (error) {
        this.$store.dispatch('notification/error', error);
      }
    },
    async submit() {
      // prevent empty title with just spaces
      this.page.title = this.page.title.trim();

      if (this.page.title.length === 0) {
        return this.$refs.dialog.error(this.$t("error.page.changeTitle.empty"));
      }

      try {
        await this.$api.pages.changeTitle(this.page.id, this.page.title);
        this.success({
          message: ":)",
          event: "page.changeTitle"
        });

      } catch (error) {
        this.$refs.dialog.error(error.message);
      }
    }
  }
};
</script>
