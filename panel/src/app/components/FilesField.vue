<template>
  <k-field
    :input="_uid"
    v-bind="$props"
    class="k-picker-field"
  >
    <!-- Actions button/dropdown -->
    <k-options-dropdown
      v-if="!disabled && actions.length"
      :options="actions"
      :text="actionsLabel"
      @option="onAction"
      slot="options"
    />

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
        ref="collection"
        v-bind="collection"
        :data-has-actions="this.actions.length > 0"
        @empty="onEmpty"
        @option="onRemove"
        @sort="onSort"
      />
    </k-dropzone>

    <!-- Drawer & Picker -->
    <k-drawer
      ref="drawer"
      :loading="drawer.loading"
      :title="label + ' / ' + $t('select')"
      :size="picker.size || 'small'"
      @close="$refs.picker.reset()"
      @submit="onSelect"
    >
      <k-dropzone @drop="onDrop">
        <k-picker
          ref="picker"
          v-model="drawer.value"
          v-bind="selector"
          @paginate="onPaginate"
          @startLoading="onLoading"
          @stopLoading="onLoaded"
        />
      </k-dropzone>
    </k-drawer>

    <k-upload
      ref="upload"
      v-bind="upload"
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

      actions.push({
        icon: "upload",
        text: this.$t("upload"),
        click: "upload"
      });

      return actions;
    },
    upload() {
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
