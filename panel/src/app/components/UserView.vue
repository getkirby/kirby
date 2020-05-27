<template>
  <k-inside class="k-user-view">
    <k-user-profile
      :can-change-avatar="!isLocked"
      :can-change-email="$permissions.changeEmail && !isLocked"
      :can-change-language="$permissions.changeRole && !isLocked"
      :can-change-role="$permissions.changeLanguage && !isLocked"
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
      ref="email"
      @success="$emit('update')"
    />
    <k-user-language-dialog
      ref="language"
      @success="$emit('update')"
    />
    <k-user-password-dialog
      ref="password"
    />
    <k-user-remove-dialog
      ref="remove"
      @success="$emit('remove')"
    />
    <k-user-rename-dialog
      ref="rename"
      @success="$emit('update')"
    />
    <k-user-role-dialog
      ref="role"
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
        case "email":
          return this.$refs.email.open(this.user.id);
        case "language":
          return this.$refs.language.open(this.user.id);
        case "password":
          return this.$refs.password.open(this.user.id);
        case "remove":
          return this.$refs.remove.open(this.user.id);
        case "rename":
          return this.$refs.rename.open(this.user.id);
        case "role":
          return this.$refs.role.open(this.user.id);
      }
    }
  }
};
</script>
