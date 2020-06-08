<template>
  <k-inside
    :breadcrumb="breadcrumb"
    :view="account ? 'account' : 'users'"
    class="k-user-view"
    search="users"
  >
    <k-user-profile
      v-bind="profile"
      @option="onOption"
    />
    <k-model-view
      v-bind="$props"
      :rename="true"
      :title="title"
      @rename="onOption('rename')"
      @option="onOption"
      v-on="$listeners"
    />

    <!-- Dialogs -->
    <k-user-email-dialog
      ref="emailDialog"
      @success="$emit('changeEmail', $event)"
    />
    <k-user-language-dialog
      ref="languageDialog"
      @success="$emit('changeLanguage', $event)"
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
      @success="$emit('changeName', $event)"
    />
    <k-user-role-dialog
      ref="roleDialog"
      @success="$emit('changeRole', $event)"
    />
    <k-upload
      ref="upload"
      @success="$emit('changeAvatar')"
    />
  </k-inside>
</template>

<script>
import ModelView from "./ModelView.vue";

export default {
  props: {
    ...ModelView.props,
    account: {
      type: Boolean,
      default: false
    },
    id: {
      type: String
    },
    profile: {
      type: Object,
      default() {
        return {};
      }
    },
    title: String
  },
  methods: {
    async onOption(option) {
      switch (option) {
        case "removeAvatar":
          await this.$api.users.deleteAvatar(this.id);
          return this.$emit("changeAvatar");
        case "uploadAvatar":
          return this.$refs.upload.open({
            url: this.$config.api + "/users/" + this.id + "/avatar",
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
