<script>
import AsyncFormDialog from "@/ui/components/AsyncFormDialog.vue";

export default {
  extends: AsyncFormDialog,
  data() {
    return {
      id: null,
      num: null,
      siblings: [],
      stati: {},
      values: {},
    };
  },
  computed: {
    fieldsetup() {
      // field setup
      let fields = {
        status: {
          name: "status",
          label: this.$t("page.changeStatus.select"),
          type: "radio",
          required: true,
          options: Object.keys(this.stati).map(key => {
            const icon = this.$model.pages.statusIcon(key);

            return {
              value: key,
              text: this.stati[key].label,
              info: this.stati[key].text,
              icon: icon.type,
              color: icon.color
            };
          })
        }
      };

      // add the position field if needed
      if (this.values.status === "listed" && this.num === "default") {
        // create the select box
        fields.position = {
          name: "position",
          label: this.$t("page.changeStatus.position"),
          type: "select",
          empty: false,
          options: this.positions(this.siblings)
        };
      }

      return fields;
    }
  },
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

      // num
      this.num = page.blueprint.num;

      // stati
      this.stati = page.blueprint.status;

      // load all siblings
      if (this.num === "default") {
        const { siblings } = await this.$api.pages.get(this.id, {
          select: ["siblings"]
        });

        this.siblings = siblings;
      }

      // form values
      this.values = {
        position: page.num,
        status: page.status,
      };

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
