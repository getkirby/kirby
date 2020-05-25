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
      :disabled="disabled || !uploads"
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
      :has-drop="!!uploads"
      @submit="onSelect"
      @drop="onDrop"
    />

    <!-- Upload -->
    <k-upload
      v-if="uploads"
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
    type: {
      type: String,
      default: "files"
    },
    uploads: {
      type: [Boolean, Object],
      default() {
        return {};
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

      if (this.uploads) {
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
        url: this.endpoints.field + "/upload",
        accept: this.uploads.accept,
        multiple: this.multiple
      }
    }
  },
  methods: {
    onAction(option, item, itemIndex) {
      switch (option) {
        case "upload":
          return this.$refs.upload.open();
        case "select":
          return this.onOpen();
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
