<template>
  <k-inside
    :breadcrumb="breadcrumb"
    class="k-user-view"
    search="users"
    view="users"
  >
    <k-user-profile
      v-bind="profile"
      @remove-avatar="onOption('avatar.remove')"
      @upload-avatar="onOption('avatar.upload')"
      @email="onOption('email')"
      @language="onOption('language')"
      @role="onOption('role')"
    />
    <k-model-view
      v-bind="$props"
      :rename="true"
      :title="title"
      @rename="onOption('rename')"
      @option="onOption"
    />

    <!-- Dialogs -->
    <k-user-email-dialog
      ref="emailDialog"
      @success="$emit('update')"
    />
    <k-user-language-dialog
      ref="languageDialog"
      @success="$emit('update')"
    />
    <k-user-password-dialog
      ref="passwordDialog"
    />
    <k-user-remove-dialog
      ref="removeDialog"
      @success="$emit('remove')"
    />
    <k-user-rename-dialog
      ref="renameDialog"
      @success="$emit('update')"
    />
    <k-user-role-dialog
      ref="roleDialog"
      @success="$emit('update')"
    />
    <k-upload
      ref="upload"
      @success="$emit('update')"
    />
  </k-inside>
</template>

<script>
import ModelView from "./ModelView.vue";

export default {
  props: {
    ...ModelView.props,
    id: String,
    profile: {
      type: Object,
      default() {
        return {};
      }
    },
    title: String
  },
  methods: {
    onOpen() {
    },
    async onOption(option) {
      switch (option) {
        case "avatar.remove":
          await this.$api.users.deleteAvatar(this.id);
          return this.$emit("update");
        case "avatar.upload":
          return this.$refs.upload.open({
            // TODO: API endpoint
            url: "",
            accept: "image/*",
            multiple: false
          });
        default:
          return this.$refs[option + "Dialog"].open(this.id);
      }
    }
  }
};
</script>
