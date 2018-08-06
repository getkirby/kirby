<template>
  <k-dialog :visible="true" class="k-files-dialog">
    <k-list :items="files" />
  </k-dialog>
</template>

<script>
export default {
  data() {
    return {
      files: []
    }
  },
  created() {
    this.fetch();
  },
  methods: {
    fetch() {
      this.$api
        .get('pages/projects+drones/files')
        .then(files => {

          this.files = files.data.map(file => {
            return {
              text: file.filename,
              image: {
                url: file.url
              }
            };
          });

        });
    }
  }
};
</script>

<style lang="scss">
.k-files-dialog .k-dialog-box {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
}
.k-files-dialog .k-dialog-body {
  max-height: none;
  flex-grow: 1;
}
</style>
