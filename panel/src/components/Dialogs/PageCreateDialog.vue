<template>
  <k-form-dialog
    ref="dialog"
    v-model="page"
    :fields="fields"
    :submit-button="$t('page.draft.create')"
    @submit="submit"
    @close="reset"
  />
</template>

<script>
import DialogMixin from "@/mixins/dialog.js";

export default {
  mixins: [DialogMixin],
  data() {
    return {
      parent: null,
      section: null,
      templates: [],
      page: this.emptyForm()
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
        template: {
          name: "template",
          label: this.$t("template"),
          type: "select",
          disabled: this.templates.length === 1,
          required: true,
          icon: "code",
          empty: false,
          options: this.templates
        }
      };
    }
  },
  watch: {
    "page.title"(title) {
      this.page.slug = this.$helper.slug(title, [this.$system.slugs, this.$system.ascii]);
    }
  },
  methods: {
    emptyForm() {
      return {
        title: "",
        slug: "",
        template: null
      };
    },
    async open(parent, blueprintApi, section) {
      this.parent  = parent;
      this.section = section;

      try {
        const response = await this.$api.get(blueprintApi, {section: section});

        this.templates = response.map(blueprint => ({
          value: blueprint.name,
          text: blueprint.title
        }));

        if (this.templates[0]) {
          this.page.template = this.templates[0].value;
        }

        this.$refs.dialog.open();

      } catch (error) {
        this.$store.dispatch("notification/error", error);
      }
    },
    async submit() {
      // prevent empty title with just spaces
      this.page.title = this.page.title.trim();

      if (this.page.title.length === 0) {
        this.$refs.dialog.error(this.$t("error.page.changeTitle.empty"));
        return;
      }

      const data = {
        template: this.page.template,
        slug: this.page.slug,
        content: {
          title: this.page.title
        }
      };

      try {
        const page = await this.$api.post(this.parent + "/children", data);

        this.success({
          route: this.$api.pages.link(page.id),
          message: ":)",
          event: "page.create"
        });

      } catch (error) {
        this.$refs.dialog.error(error.message);
      }
    },
    reset() {
      this.page = this.emptyForm();
    }
  }
};
</script>
