<template>
  <kirby-dialog
    ref="dialog"
    :button="$t('create')"
    :notification="notification"
    size="medium"
    theme="positive"
    @submit="$refs.form.submit()"
  >
    <kirby-form
      ref="form"
      :fields="fields"
      v-model="page"
      @submit="submit"
    />
  </kirby-dialog>
</template>

<script>
import DialogMixin from "@/mixins/dialog.js";
import slug from "@/ui/helpers/slug.js";

export default {
  mixins: [DialogMixin],
  data() {
    return {
      notification: null,
      parent: null,
      section: null,
      templates: [],
      page: {
        title: null,
        template: null
      }
    };
  },
  computed: {
    fields() {
      let fields = {
        title: {
          label: this.$t("page.title"),
          type: "text",
          required: true,
          icon: "title"
        }
      };

      if (this.templates.length > 1) {
        fields.template = {
          name: "template",
          label: this.$t("page.template"),
          type: "select",
          required: true,
          options: this.templates
        };
      }

      return fields;
    }
  },
  methods: {
    open(parent, section, templates) {
      this.parent = parent;
      this.section = section;
      this.templates = templates;
      this.page.template = templates[0].value;
      this.$refs.dialog.open();
    },
    submit() {
      const data = {
        template: this.page.template,
        slug: slug(this.page.title),
        content: {
          title: this.page.title
        }
      };

      this.$api
        .post(this.parent + "/" + this.section, data)
        .then(page => {
          this.success({
            route: this.$api.page.link(page.id),
            message: this.$t("page.created"),
            event: "page.create"
          });
        })
        .catch(error => {
          this.$refs.dialog.error(error.message);
        });
    }
  }
};
</script>
