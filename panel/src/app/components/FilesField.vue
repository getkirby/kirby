<template>
  <k-field :input="_uid" v-bind="$props">

    <!-- Actions button/dropdown -->
    <template v-slot:options>
      <k-options-dropdown
        v-if="hasActions"
        v-bind="actionsOptions"

        @option="onAction"
      />
    </template>

    <!-- Error -->
    <k-error-items
      v-if="error"
      :layout="layout"
      :limit="value.length"
    >
      {{ error }}
    </k-error-items>

    <!-- Collection -->
    <k-dropzone
      v-else
      :disabled="disabled"
      @drop="onDrop"
    >
      <k-collection
        v-bind="collectionOptions"
        @empty="onEmpty"
        @option="onRemove"
        @sort="onSort"
      />
    </k-dropzone>

    <!-- Drawer & Picker -->
    <k-drawer
      v-if="hasOptions"
      v-bind="drawerOptions"
      ref="drawer"
      @close="$refs.picker.reset()"
      @submit="onSelect"
    >
      <k-dropzone @drop="onDrop">
        <k-picker
          ref="picker"
          v-model="drawer.value"
          v-bind="pickerOptions"
          @paginate="onPaginate"
          @startLoading="onLoading"
          @stopLoading="onLoaded"
        />
      </k-dropzone>
    </k-drawer>

    <k-upload
      v-if="upload"
      ref="upload"
      v-bind="uploadOptions"
      @success="onUpload"
    />
  </k-field>
</template>

<script>
import PickerField from "@/ui/components/PickerField.vue";

// TODO: implement actual API instead
import { File, Files } from "../../../storybook/data/PickerItems.js";

export default {
  extends: PickerField,
  props: {
    empty: {
      type: [String, Object],
      default() {
        return {
          icon: "file",
          text: this.$t("field.files.empty")
        };
      }
    },
    upload: {
      type: Boolean,
      default: true
    }
  },
  computed: {
    actions() {
      let actions = [];

      if (this.hasOptions) {
        actions.push({
          icon: "circle-nested",
          text: this.$t("select"),
          click: "select"
        });
      }

      if (this.upload) {
        actions.push({
          icon: "upload",
          text: this.$t("upload"),
          click: "upload"
        });
      }

      return actions;
    },
    uploadOptions() {
      return {
        url: "api/upload",
        multiple: this.multiple
      }
    }
  },
  methods: {
    async getItems(ids) {
      await new Promise(r => setTimeout(r, 1500));
      return ids.map(id => File(id));
    },
    async getOptions({page, limit, parent, search}) {
      await new Promise(r => setTimeout(r, 1500));
      return Files(page, limit, parent, search);
    },
    onAction(option, item, itemIndex) {
      switch (option) {
        case "upload":
          this.$refs.upload.open();
          // TODO: add file upload dialog
          break;
        case "select":
          this.onOpen();
          break;
      }
    },
    onDrop(event) {
      this.$refs.upload.drop(event);
    },
    onUpload(files, response) {
      // TODO: handle result and add to array
      this.selected.push("1");
      this.onInput(true);

      // make sure to update drawer selection for the
      // case that the drawer is currently open
      this.drawer.value = this.$helper.clone(this.selected);
    }
  }
}
</script>
