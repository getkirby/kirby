<template>
  <k-file-view
    :file="file"
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
    }
  }
}
</script>
