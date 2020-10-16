<template>
  <k-form-dialog
    ref="dialog"
    v-model="form"
    :fields="fields"
    :submit-button="$t('change')"
    @submit="submit"
  />
</template>

<script>
import DialogMixin from "@/mixins/dialog.js";

export default {
  mixins: [DialogMixin],
  data() {
    return {
      file: {
        id: null
      },
      form: {
        position: null
      },
      siblings: [],
      api: null
    };
  },
  computed: {
    fields() {
      return {
        position: {
          name: "position",
          label: this.$t("file.sort"),
          type: "select",
          empty: false,
          options: this.sortingOptions
        }
      };
    },
    sortingOptions() {
      let options = [];
      let index = 0;

      this.siblings.forEach(sibling => {
        if (sibling.id === this.file.id || sibling.num < 1) {
          return false;
        }

        index++;

        options.push({
          value: index,
          text: index
        });

        options.push({
          value: sibling.id,
          text: sibling.filename,
          disabled: true
        });
      });

      options.push({
        value: index + 1,
        text: index + 1
      });

      return options;
    }
  },
  methods: {
    async open(parent, file, api) {

      try {
        this.file      = file;
        const response = await this.$api.post(parent + "/files/search");
        this.siblings  = response.data;
        this.form.position = this.siblings.findIndex(x => x.id === file.id) + 1;
        this.api = api;
        this.$refs.dialog.open();

      } catch (error) {
        this.$store.dispatch('notification/error', error);
      }
    },
    async submit() {
      try {
        const oldIndex = this.siblings.findIndex(x => x.id === this.file.id);
        const newIndex = this.form.position - 1;
        const files = this.$helper.clone(this.siblings);
        files.splice(oldIndex, 1);
        files.splice(newIndex, 0, this.file);

        await this.$api.patch(this.api + "/files/sort", {
          files: files.map(file => file.id),
          index: 0
        });

        this.success({
          message: ":)",
          event: "file.sort"
        });

      } catch (error) {
        this.$refs.dialog.error(error.message);
      }
    }
  }
};
</script>
