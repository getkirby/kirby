<template>
  <k-inside class="k-file-view">
    <k-file-preview v-bind="preview" />
    <k-model-view
      :columns="columns"
      :rename="true"
      :tab="tab"
      :tabs="tabs"
      :title="file.filename"
      @rename="onOption('rename')"
    >
      <template v-slot:options>
        <k-button
          :responsive="true"
          :text="$t('open')"
          icon="open"
          @click="onOpen"
        />
        <k-dropdown>
          <k-button
            :disabled="isLocked"
            :responsive="true"
            :text="$t('settings')"
            icon="cog"
            @click="$refs.settings.toggle()"
          />
          <k-dropdown-content
            ref="settings"
            :options="options"
            @option="onOption"
          />
        </k-dropdown>
      </template>
    </k-model-view>

    <!-- Dialogs -->
    <k-file-rename-dialog
      ref="renameDialog"
      @success="$emit('renamed', $event)"
    />
    <k-file-remove-dialog
      ref="removeDialog"
      @success="$emit('removed', $event)"
    />
    <k-upload
      ref="replaceDialog"
      @success="$emit('replaced', $event)"
    />
  </k-inside>
</template>

<script>
export default {
  props: {
    columns: {
      type: Array,
      default() {
        return [];
      }
    },
    file: {
      type: Object,
      default() {
        return {};
      }
    },
    isLocked: {
      type: Boolean,
      default: false
    },
    options: {
      type: Array,
      default() {
        return [];
      }
    },
    tabs: {
      type: Array,
      default() {
        return []
      }
    },
    tab: {
      type: String,
      default: ""
    }
  },
  computed: {
    preview() {
      return {
        ...this.file,
        ...this.file.dimensions || {},
        image: this.file.url,
        link: this.file.url,
        size: this.file.niceSize,
      };
    }
  },
  methods: {
    onOpen() {
      window.open(this.file.url);
    },
    onOption(option) {
      switch (option) {
        case "rename":
          return this.$refs.renameDialog.open(
            this.file.parent.guid,
            this.file.filename
          );
        case "replace":
          return this.$refs.replaceDialog.open({
            url: this.file.replaceApi,
            accept: this.file.mime,
            multiple: false
          });
        case "remove":
          return this.$refs.removeDialog.open(
            this.file.parent.guid,
            this.file.filename
          );
      }
    }
  }
};
</script>
