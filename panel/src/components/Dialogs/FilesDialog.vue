<template>
  <k-dialog
    ref="dialog"
    class="k-files-dialog"
    size="medium"
    @cancel="$emit('cancel')"
    @submit="submit"
  >
    <template v-if="issue">
      <k-box :text="issue" theme="negative" />
    </template>
    <template v-else>
      <k-list v-if="files.length">
        <k-list-item
          v-for="(file, index) in files"
          :key="file.filename"
          :text="file.filename"
          :image="file.image"
          :icon="file.icon"
          @click="toggle(index)"
        >
          <k-button
            v-if="file.selected"
            slot="options"
            :autofocus="true"
            :icon="checkedIcon"
            :tooltip="$t('remove')"
            theme="positive"
          />
          <k-button
            v-else
            slot="options"
            :autofocus="true"
            :tooltip="$t('select')"
            icon="circle-outline"
          />
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
        api: null,
        selected: []
      }
    };
  },
  computed: {
    multiple() {
      return this.options.multiple === true && this.options.max !== 1;
    },
    checkedIcon() {
      return this.multiple === true ? "check" : "circle-filled";
    }
  },
  methods: {
    selected() {
      return this.files.filter(file => file.selected);
    },
    submit() {
      this.$emit("submit", this.selected());
      this.$refs.dialog.close();
    },
    toggle(index) {
      if (this.multiple === false) {
        this.files = this.files.map(file => {
          file.selected = false;
          return file;
        });

        this.files[index].selected = true;
      } else {
        if (!this.files[index].selected) {
          if (this.options.max && this.options.max <= this.selected().length) {
            return;
          }
          this.files[index].selected = true;
        } else {
          this.files[index].selected = false;
        }
      }
    },
    open(files, options) {
      this.files = files;
      this.options = options;
      this.$refs.dialog.open();
    }
  }
};
</script>

<style lang="scss">
.k-files-dialog .k-list-item {
  cursor: pointer;
}
</style>
