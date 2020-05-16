<script>
import AsyncFormDialog from "@/ui/components/AsyncFormDialog.vue";

export default {
  extends: AsyncFormDialog,
  watch: {
    "values.title"(title) {
      this.values.slug = title;
    }
  },
  methods: {
    async load(parent, section) {
      this.parent  = parent;
      this.section = section;

      // load all available blueprints based on the section
      const blueprints = await this.$model.pages.blueprints(parent, section);

      // create the options for the template select field
      const templates = blueprints.map(blueprint => {
        return {
          value: blueprint.name,
          text: blueprint.title
        };
      });

      // always start with an empty form
      this.values = {
        title: "",
        slug: "",
        template: null
      };

      // assign the first available template
      if (templates[0]) {
        this.values.template = templates[0].value;
      }

      // field setup
      this.fields = {
        title: {
          icon: "title",
          label: this.$t("title"),
          required: true,
          trim: true,
          type: "text",
        },
        slug: {
          label: this.$t("slug"),
          type: "text",
          required: true,
          counter: false,
          icon: "url",
          slug: true,
        },
        template: {
          name: "template",
          label: this.$t("template"),
          type: "select",
          disabled: templates.length === 1,
          required: true,
          icon: "code",
          empty: false,
          options: templates
        }
      };

      this.submitButton = this.$t("page.draft.create");
    },
    async submit() {
      // create new page
      const page = await this.$model.pages.create(this.parent, {
        content: {
          title: this.values.title
        },
        slug: this.values.slug,
        template: this.values.template,
      });

      // route to new page
      const path = this.link(page.id);
      this.$router.push(path);

      return page;
    },
    async validate() {
      if (this.values.title.length === 0) {
        throw this.$t("error.page.changeTitle.empty");
      }
    }
  }
}
</script>
