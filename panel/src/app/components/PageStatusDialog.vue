<script>
import AsyncFormDialog from "@/ui/components/AsyncFormDialog.vue";

export default {
  extends: AsyncFormDialog,
  methods: {
    async load(id) {
      const page = await this.$api.pages.get(id, {
        select: [
          "blueprint",
          "errors",
          "id",
          "num",
          "status",
        ]
      });

      // check for permissions
      if (page.blueprint.options.changeStatus === false) {
        throw this.$t("error.page.changeStatus.permission");
      }

      // check if the page is error-free
      if (
        page.status === "draft" &&
        Object.keys(page.errors).length > 0
      ) {
        throw new Error({
          message: this.$t("error.page.changeStatus.incomplete"),
          details: page.errors
        });
      }

      // store the id for the submit request
      this.id = id;

      // form values
      this.values = {
        status: page.status
      };

      // field setup
      this.fields = {
        status: {
          name: "status",
          label: this.$t("page.changeStatus.select"),
          type: "radio",
          required: true,
          options: Object.keys(page.blueprint.status).map(key => {
            return {
              value: key,
              text: page.blueprint.status[key].label,
              info: page.blueprint.status[key].text,
              icon: page.blueprint.status[key].icon,
              color: page.blueprint.status[key].color
            };
          })
        }
      };

      // add the position field if needed
      if (page.status === "listed" && page.blueprint.num === "default") {
        // load all siblings
        const { siblings } = await this.$api.pages.get(id, {
          select: ["siblings"]
        });

        // create the select box
        this.fields.position = {
          name: "position",
          label: this.$t("page.changeStatus.position"),
          type: "select",
          empty: false,
          options: this.positions(siblings)
        };

        // add the selected position to the form values
        this.values.position = page.num || (siblings.length + 1)
      }

      // set up the submit button
      this.submitButton = this.$t("change");
    },
    /**
     * Build an array of options for the
     * position select field.
     */
    positions(siblings) {
      let options = [];
      let index = 0;

      siblings = Array.isArray(siblings) ? siblings : [];

      siblings.forEach(sibling => {
        if (sibling.id === this.id || sibling.num < 1) {
          return false;
        }

        index++;

        options.push({
          value: index,
          text: index
        });
        options.push({
          value: sibling.id,
          text: sibling.title,
          disabled: true
        });
      });

      options.push({
        value: index + 1,
        text: index + 1
      });

      return options;
    },
    async submit() {
      return await this.$model.pages.changeStatus(
        this.id,
        this.values.status,
        this.values.position || 1
      );
    }
  }
}
</script>
