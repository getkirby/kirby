<template>
  <k-inside
    class="k-user-view"
    search="users"
  >
    <k-user-profile
      :can-change-avatar="lock === false"
      :can-change-email="$permissions.changeEmail && lock === false"
      :can-change-language="$permissions.changeRole && lock === false"
      :can-change-role="$permissions.changeLanguage && lock === false"
      :user="user"
      @remove-avatar="onOption('avatar.remove')"
      @upload-avatar="onOption('avatar.upload')"
      @email="onOption('email')"
      @language="onOption('language')"
      @role="onOption('role')"
    />
    <k-model-view
      v-bind="$props"
      :rename="true"
      :title="user.name || user.email"
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
    user: {
      type: Object,
      default() {
        return {};
      }
    }
  },
  methods: {
    onOpen() {
    },
    async onOption(option) {
      switch (option) {
        case "avatar.remove":
          await this.$api.users.deleteAvatar(this.user.id);
          return this.$emit("update");
        case "avatar.upload":
          return this.$refs.upload.open({
            url: this.user.avatarApi,
            accept: "image/*",
            multiple: false
          });
        default:
          return this.$refs[option + "Dialog"].open(this.user.id);
      }
    }
  }
};
</script>
