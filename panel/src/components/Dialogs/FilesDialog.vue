<template>
  <k-dialog ref="dialog" class="k-files-dialog" size="medium" @cancel="$emit('cancel')" @submit="submit">
    <template v-if="issue">
      <k-box theme="negative" :text="issue" />
    </template>
    <template v-else>
      <k-list v-if="files.length">
        <k-list-item
          v-for="(file, index) in files"
          :key="file.filename"
          :text="file.filename"
          :image="{ url: file.url }"
          @click="toggle(index)"
        >
          <k-button v-if="file.selected" theme="positive" icon="check" slot="options" />
        </k-list-item>
      </k-list>
      <k-empty v-else icon="image">
        No files to select
      </k-empty>
    </template>
  </k-dialog>
</template>

<script>
export default {
  data() {
    return {
      files: [],
      issue: null,
      options: {
        max: null,
        multiple: true,
        parent: null,
        selected: []
      }
    }
  },
  methods: {
    fetch() {

      this.files = [];

      return this.$api
        .get(this.options.parent + "/files", {view: "compact"})
        .then(files => {

          const selected = this.options.selected || [];

          this.files = files.data.map(file => {
            file.selected = selected.indexOf(file.id) !== -1;
            return file;
          });

        })
        .catch(e => {
          this.files = [];
          this.issue = e.message;
        });
    },
    selected() {
      return this.files.filter(file => file.selected);
    },
    submit() {
      this.$emit("submit", this.selected());
      this.$refs.dialog.close();
    },
    toggle(index) {
      if (this.options.multiple === false) {
        this.files = this.files.map(file => {
          file.selected = false;
          return file;
        });
      }

      if (!this.files[index].selected) {
        if (this.options.max && this.options.max <= this.selected().length) {
          return;
        }
        this.files[index].selected = true;
      } else {
        this.files[index].selected = false;
      }
    },
    open(options) {
      this.options = options;
      this.fetch().then(() => {
        this.$refs.dialog.open();
      });
    }
  }
};
</script>

<style lang="scss">
/* .k-files-dialog .k-dialog-box {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
}
.k-files-dialog .k-dialog-body {
  max-height: none;
  flex-grow: 1;
} */
.k-files-dialog .k-list-item {
  cursor: pointer;
}
.k-files-dialog .k-empty {
  border: 0;
}
</style>
