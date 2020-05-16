<script>
import AsyncFormDialog from "@/ui/components/AsyncFormDialog.vue";

export default {
  extends: AsyncFormDialog,
  methods: {
    async load(id) {

      const { template, blueprints } = await this.$api.pages.get(id, {
        select: ["template", "blueprints"]
      });

      this.id     = id;
      this.values = { template };

      // throw an error if there are no available blueprints
      if (blueprints.length <= 1) {
        throw this.$t("error.page.changeTemplate.invalid", {
          slug: this.id
        });
      }

      // prepare the options for the select box
      const options = blueprints.map(blueprint => {
        return {
          text: blueprint.title,
          value: blueprint.name
        };
      });

      this.fields = {
        template: {
          label: this.$t("template"),
          type: "select",
          required: true,
          empty: false,
          options: options,
          icon: "template"
        }
      };

      this.submitButton = this.$t("change");

    },
    async submit() {
      return await this.$model.pages.changeTemplate(
        this.id,
        this.values.template
      );
    },
  }
}
</script>
