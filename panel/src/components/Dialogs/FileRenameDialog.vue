<template>
  <k-dialog
    ref="dialog"
    :button="$t('rename')"
    size="medium"
    theme="positive"
    @submit="$refs.form.submit()"
  >
    <k-form
      ref="form"
      :fields="fields"
      v-model="file"
      @submit="submit"
      @input="file.name = sluggify(file.name)"
    />
  </k-dialog>
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
    },
    slugs() {
      return this.$store.state.languages.default ? this.$store.state.languages.default.rules : this.system.slugs;
    },
    system() {
      return this.$store.state.system.info;
    }
  },
  methods: {
    open(parent, filename) {
      this.$api.files
        .get(parent, filename, {
          select: ["id", "filename", "name", "extension"]
        })
        .then(file => {
          this.file   = file;
          this.parent = parent;
          this.$refs.dialog.open();
        })
        .catch (error => {
          this.$store.dispatch('notification/error', error);
        });
    },
    sluggify(input) {
      return this.$helper.slug(input, [this.slugs, this.system.ascii], ".");
    },
    submit() {
      // prevent empty name with just spaces
      this.file.name = this.file.name.trim();

      if (this.file.name.length === 0) {
        this.$refs.dialog.error(this.$t("error.file.changeName.empty"));
        return;
      }

      this.$api.files
        .rename(this.parent, this.file.filename, this.file.name)
        .then(file => {

          // move form changes
          this.$store.dispatch("form/move", {
            old: this.$store.getters["form/id"](this.file.id),
            new: this.$store.getters["form/id"](file.id)
          });

          this.$store.dispatch("notification/success", ":)");
          this.$emit("success", file);

          this.$events.$emit("file.changeName", file);

          this.$refs.dialog.close();
        })
        .catch(error => {
          this.$refs.dialog.error(error.message);
        });
    }
  }
};
</script>
