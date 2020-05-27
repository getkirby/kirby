<template>
  <k-inside class="k-user-view">
    <k-user-profile
      :can-change-avatar="true"
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
      :columns="columns"
      :rename="true"
      :tab="tab"
      :tabs="tabs"
      :title="user.name || user.email"
      @rename="onOption('rename')"
    >
      <template v-slot:options>
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
export default {
  props: {
    columns: {
      type: Array,
      default() {
        return [];
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
    },
    user: {
      type: Object,
      default() {
        return {};
      }
    }
  },
  computed: {
    options() {
      return async () => this.$model.users.options(this.user.id);
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
