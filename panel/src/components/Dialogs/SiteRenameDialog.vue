<template>
  <k-form-dialog
    ref="dialog"
    v-model="site"
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
      site: {
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
    async open() {
      try {
        this.site = await this.$api.site.get({ select: ["title"] });
        this.$refs.dialog.open();

      } catch (error) {
        this.$store.dispatch('notification/error', error);
      }
    },
    async submit() {
      // prevent empty title with just spaces
      this.site.title = this.site.title.trim();

      if (this.site.title.length === 0) {
        this.$refs.dialog.error(this.$t("error.site.changeTitle.empty"));
        return;
      }

      try {
        await this.$api.site.changeTitle(this.site.title);
        this.$store.dispatch("system/title", this.site.title);
        this.success({
          message: ":)",
          event: "site.changeTitle"
        });

      } catch (error) {
        this.$refs.dialog.error(error.message);
      }
    }
  }
};
</script>
