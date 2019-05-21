<template>
  <k-dialog
    ref="dialog"
    :button="$t('duplicate')"
    :notification="notification"
    size="medium"
    theme="positive"
    @submit="$refs.form.submit()"
  >
    <k-form
      ref="form"
      :fields="fields"
      :novalidate="true"
      v-model="page"
      @submit="submit"
    />
  </k-dialog>
</template>

<script>
import DialogMixin from "@/mixins/dialog.js";
import slug from "@/helpers/slug.js";

export default {
  mixins: [DialogMixin],
  data() {
    return {
      notification: null,
      page: {
        id: null,
        files: false,
        slug: '',
        title: '',
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
          icon: "title"
        },
        slug: {
          label: this.$t("slug"),
          type: "text",
          required: true,
          counter: false,
          icon: "url"
        },
        files: {
          label: "Copy files",
          type: "toggle",
          required: true,
        }
      };
    }
  },
  watch: {
    "page.title"(title) {
      this.page.slug = slug(title);
    }
  },
  methods: {
    open(id) {
      this.$api.pages
        .get(id)
        .then(page => {
          this.page.id    = page.id;
          this.page.title = page.title + " copy";
          this.page.slug  = page.slug + "-copy";
          this.$refs.dialog.open();
        })
        .catch(error => {
          this.$store.dispatch("notification/error", error);
        });
    },
    submit() {
      this.$api.pages
        .duplicate(this.page.id, {
          files: this.page.files,
          slug: this.page.slug,
          title: this.page.title,
        })
        .then(page => {
          this.success({
            route: this.$api.pages.link(page.id),
            message: ":)",
            event: "page.duplicate"
          });
        })
        .catch(error => {
          this.$refs.dialog.error(error.message);
        });
    }
  }
};
</script>
