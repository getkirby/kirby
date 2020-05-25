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
          icon="open"
          @click="onOpen"
        >
          {{ $t("open") }}
        </k-button>
        <k-dropdown>
          <k-button
            :responsive="true"
            :disabled="isLocked"
            icon="cog"
            @click="$refs.settings.toggle()"
          >
            {{ $t('settings') }}
          </k-button>
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
      ref="rename"
      @success="$emit('rename', $event)"
    />
    <k-file-remove-dialog
      ref="remove"
      @success="$emit('remove')"
    />
    <k-upload
      ref="replace"
      @success="$emit('update')"
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
    options() {
      return async () => this.$model.files.options(
        this.file.parent,
        this.file.filename
      );
    },
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
          return this.$refs.rename.open(
            this.file.parent,
            this.file.filename
          );
        case "replace":
          return this.$refs.replace.open({
            url: this.file.replaceApi,
            accept: this.file.mime,
            multiple: false
          });
        case "remove":
          return this.$refs.remove.open(
            this.file.parent,
            this.file.filename
          );
      }
    }
  }
};
</script>
