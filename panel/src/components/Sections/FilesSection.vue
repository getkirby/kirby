<script>
import ModelsSection from "@/components/Sections/ModelsSection.vue";

export default {
  extends: ModelsSection,
  computed: {
    canAdd() {
      return this.$permissions.files.create && this.options.upload !== false;
    },
    canDrop() {
      return this.canAdd !== false;
    },
    emptyProps() {
      return {
        icon: "image",
        text: this.options.empty || this.$t("files.empty")
      };
    },
    items() {
      return this.data.map((file) => {
        file.sortable = this.options.sortable;
        file.column = this.column;
        file.options = this.$dropdown(file.link, {
          query: {
            view: "list",
            update: this.options.sortable,
            delete: this.data.length > this.options.min
          }
        });

        // add data-attributes info for item
        file.data = {
          "data-id": file.id,
          "data-template": file.template
        };

        return file;
      });
    },
    type() {
      return "files";
    },
    uploadProps() {
      return {
        ...this.options.upload,
        url: this.$urls.api + "/" + this.options.upload.api
      };
    }
  },
  created() {
    this.load();
    this.$events.$on("model.update", this.reload);
    this.$events.$on("file.sort", this.reload);
  },
  destroyed() {
    this.$events.$off("model.update", this.reload);
    this.$events.$off("file.sort", this.reload);
  },
  methods: {
    onAction(action, file) {
      if (action === "replace") {
        this.replace(file);
      }
    },
    onAdd() {
      if (this.canAdd) {
        this.$refs.upload.open(this.uploadProps);
      }
    },
    onDrop(files) {
      if (this.canAdd) {
        this.$refs.upload.drop(files, this.uploadProps);
      }
    },
    async onSort(items) {
      if (this.options.sortable === false) {
        return false;
      }

      this.isProcessing = true;

      try {
        await this.$api.patch(this.options.apiUrl + "/files/sort", {
          files: items.map((item) => item.id),
          index: this.pagination.offset
        });
        this.$store.dispatch("notification/success", ":)");
        this.$events.$emit("file.sort");
      } catch (error) {
        this.reload();
        this.$store.dispatch("notification/error", error.message);
      } finally {
        this.isProcessing = false;
      }
    },
    onUpload() {
      this.$events.$emit("file.create");
      this.$events.$emit("model.update");
      this.$store.dispatch("notification/success", ":)");
    },
    replace(file) {
      this.$refs.upload.open({
        url: this.$urls.api + "/" + file.link,
        accept: "." + file.extension + "," + file.mime,
        multiple: false
      });
    }
  }
};
</script>
