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

    <!-- Drawer with picker -->
    <component
      :is="'k-' + type + '-dialog'"
      ref="dialog"
      v-bind="dialogOptions"
      :has-drop="true"
      @submit="onSelect"
      @drop="onDrop"
    />

    <!-- Upload -->
    <k-upload
      v-if="upload"
      ref="upload"
      v-bind="uploadOptions"
      @success="onUpload"
    />
  </k-field>
</template>

<script>
import ModelsField from "@/app/components/ModelsField.vue";

export default {
  extends: ModelsField,
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
    items: {
      type: Function,
      async default(ids) {
        const params = { ids: JSON.stringify(this.selected) };
        // TODO: actual API endpoint
        return this.$api.get("field/files/items", params);
      },
    },
    type: {
      type: String,
      default: "files"
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
    }
  }
}
</script>
