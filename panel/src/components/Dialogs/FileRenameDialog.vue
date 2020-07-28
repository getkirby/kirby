<template>
  <k-form-dialog
    ref="dialog"
    v-model="file"
    :fields="fields"
    :submit-button="$t('rename')"
    @input="file.name = sluggify(file.name)"
    @submit="submit"
  />
</template>

<script>
import DialogMixin from "@/mixins/dialog.js";

export default {
  mixins: [DialogMixin],
  data() {
    return {
      parent: null,
      file: {
        id: null,
        name: null,
        filename: null,
        extension: null
      }
    };
  },
  computed: {
    fields() {
      return {
        name: {
          label: this.$t("name"),
          type: "text",
          required: true,
          icon: "title",
          after: "." + this.file.extension,
          preselect: true
        }
      };
    }
  },
  methods: {
    async open(parent, filename) {
      try {
        this.file = await this.$api.files.get(parent, filename, {
          select: ["id", "filename", "name", "extension"]
        });
        this.parent = parent;
        this.$refs.dialog.open();

      } catch (error) {
        this.$store.dispatch('notification/error', error);
      }
    },
    sluggify(input) {
      return this.$helper.slug(
        input,
        [this.$system.slugs, this.$system.ascii],
        "@._-"
      );
    },
    async submit() {
      // prevent empty name with just spaces
      this.file.name = this.file.name.trim();

      if (this.file.name.length === 0) {
        this.$refs.dialog.error(this.$t("error.file.changeName.empty"));
        return;
      }

      try {
        const file = await this.$api.files.changeName(
          this.parent,
          this.file.filename,
          this.file.name
        );

        // move form changes
        this.$store.dispatch("content/move", [
          "files/" + this.file.id,
          "files/" + file.id
        ]);

        this.$store.dispatch("notification/success", ":)");
        this.$emit("success", file);
        this.$events.$emit("file.changeName", file);
        this.$refs.dialog.close();

      } catch (error) {
        this.$refs.dialog.error(error.message);
      }
    }
  }
};
</script>
