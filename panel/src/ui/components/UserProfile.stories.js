import { action } from "@storybook/addon-actions";

export default {
  title: "UI | Layout / User Profile"
};

export const regular = () => ({
  computed: {
    avatar() {
      return {
        url: 'https://source.unsplash.com/user/erondu/400x400'
      };
    },
    canAvatar() {
      return true;
    },
    canEmail() {
      return true;
    },
    canLanguage() {
      return true;
    },
    canRole() {
      return true;
    },
    user() {
      return {
        email: 'homer@simpson.com',
        role: {
          title: 'Admin'
        },
        language: 'German',
        avatar: this.avatar
      };
    }
  },
  methods: {
    onRemoveAvatar: action("removeAvatar"),
    onUploadAvatar: action("uploadAvatar"),
    onEmail: action("email"),
    onLanguage: action("language"),
    onRole: action("role"),
  },
  template: `
    <k-user-profile
      :user="user"
      :can-change-avatar="canAvatar"
      :can-change-email="canEmail"
      :can-change-language="canLanguage"
      :can-change-role="canRole"
      @remove-avatar="onRemoveAvatar"
      @upload-avatar="onUploadAvatar"
      @email="onEmail"
      @language="onLanguage"
      @role="onRole"
    />
  `
});

export const noAvatar = () => ({
  extends: regular(),
  computed: {
    avatar() {
      return {};
    }
  }
});

export const disabledAvatar = () => ({
  extends: regular(),
  computed: {
    canAvatar() {
      return false;
    }
  }
});

export const disabledEmail = () => ({
  extends: regular(),
  computed: {
    canEmail() {
      return false;
    }
  }
});

export const disabledLanguage = () => ({
  extends: regular(),
  computed: {
    canLanguage() {
      return false;
    }
  }
});

export const disabledRole = () => ({
  extends: regular(),
  computed: {
    canRole() {
      return false;
    }
  }
});
