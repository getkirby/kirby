<script>
import ModelsSection from "@/components/Sections/ModelsSection.vue";

export default {
  extends: ModelsSection,
  computed: {
    canAdd() {
      return this.options.add && this.$permissions.pages.create;
    },
    items() {
      return this.data.map((page) => {
        const isEnabled = page.permissions.changeStatus !== false;

        page.flag = {
          status: page.status,
          tooltip: this.$t("page.status"),
          disabled: !isEnabled,
          click: () => this.$dialog(page.link + "/changeStatus")
        };

        page.sortable = page.permissions.sort && this.options.sortable;
        page.deletable = this.data.length > this.options.min;
        page.column = this.column;
        page.options = this.$dropdown(page.link, {
          query: {
            view: "list",
            delete: page.deletable,
            sort: page.sortable
          }
        });

        // add data-attributes info for item
        page.data = {
          "data-id": page.id,
          "data-status": page.status,
          "data-template": page.template
        };

        return page;
      });
    }
  },
  created() {
    this.load();
    this.$events.$on("page.changeStatus", this.reload);
    this.$events.$on("page.sort", this.reload);
  },
  destroyed() {
    this.$events.$off("page.changeStatus", this.reload);
    this.$events.$off("page.sort", this.reload);
  },
  methods: {
    onAdd() {
      if (this.canAdd) {
        this.$dialog("pages/create", {
          query: {
            parent: this.options.link || this.parent,
            view: this.parent,
            section: this.name
          }
        });
      }
    },
    async onChange(event) {
      let type = null;

      if (event.added) {
        type = "added";
      }

      if (event.moved) {
        type = "moved";
      }

      if (type) {
        this.isProcessing = true;

        const element = event[type].element;
        const position = event[type].newIndex + 1 + this.pagination.offset;

        try {
          await this.$api.pages.status(element.id, "listed", position);
          this.$store.dispatch("notification/success", ":)");
          this.$events.$emit("page.sort", element);
        } catch (error) {
          this.$store.dispatch("notification/error", {
            message: error.message,
            details: error.details
          });

          await this.reload();
        } finally {
          this.isProcessing = false;
        }
      }
    }
  }
};
</script>
