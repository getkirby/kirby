<template>
  <k-file-view
    :file="file"
    :options="options"
    @removed="onRemoved"
    @renamed="onRenamed"
    @replaced="onReplaced"
  />
</template>
<script>
export default {
  props: {
    parent: {
      type: String
    },
    filename: {
      type: String
    }
  },
  data() {
    return {
      file: {},
      options: []
    };
  },
  created() {
    this.load();
  },
  watch: {
    "$route": "load",
  },
  methods: {
    async load() {
      this.file    = await this.$api.files.get(this.parent, this.filename);
      this.options = this.$model.files.dropdown(this.file.options);
    },
    onRemoved() {
      const path = this.$model.pages.link(this.parent);
      this.$router.push(path);
    },
    onRenamed(file) {
      const path = this.$model.files.link(this.parent, file.filename);
      this.$router.push(path);
    },
    onReplaced(file) {
      this.load();
    }
  }
}
</script>
