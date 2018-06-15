<template>
  <kirby-dialog
    ref="dialog"
    :button="$t('rename')"
    size="medium"
    theme="positive"
    @submit="$refs.form.submit()"
  >
    <kirby-form
      ref="form"
      :fields="fields"
      v-model="file"
      @submit="submit"
      @input="file.name = sluggify(file.name)"
    />
  </kirby-dialog>
</template>

<script>
import DialogMixin from "@/mixins/dialog.js";
import slug from "@ui/helpers/slug.js";

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
          label: this.$t("file.name"),
          type: "text",
          required: true,
          icon: "title",
          postfix: "." + this.file.extension,
          preselect: true
        }
      };
    }
  },
  methods: {
    open(parent, filename) {
      this.parent = parent;
      this.$api.file
        .get(parent, filename, {
          select: ["id", "filename", "name", "extension"]
        })
        .then(file => {
          this.file = file;
          this.$refs.dialog.open();
        })
        .catch (error => {
          this.$store.dispatch('notification/error', error);
        });
    },
    sluggify(input) {
      return slug(input);
    },
    submit() {
      this.$api.file
        .rename(this.parent, this.file.filename, this.file.name)
        .then(file => {
          let payload = {
            message: this.$t("file.renamed"),
            event: "file.changeName"
          };

          if (this.$route.name === "File") {
            payload.route = this.$api.file.link(this.parent, file.filename);
          }

          this.success(payload);
        })
        .catch(error => {
          this.$refs.dialog.error(error.message);
        });
    }
  }
};
</script>
