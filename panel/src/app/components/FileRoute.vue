<template>
  <k-file-view
    :file="file"
    @remove="onRemoved"
    @rename="onRename"
    @update="load"
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
      file: {}
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
      this.file = await this.$model.files.get(this.parent, this.filename);
    },
    onRemoved() {
      const path = this.$model.pages.link(this.parent);
      this.$router.push(path);
    },
    onRename(file) {
      const path = this.$model.files.link(this.parent, file.filename);
      this.$router.push(path);
    }
  }
}
</script>
