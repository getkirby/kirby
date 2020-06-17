import UsersView from "./UsersView.vue";
import Users from "../../../storybook/data/Users.js";

export default {
  title: "App | Views / Users",
  component: UsersView
};

export const regular = () => ({
  computed: {
    loading() {
      return false;
    },
    roles() {
      return [
        { name: "admin", title: "Admin" },
        { name: "editor", title: "Editor" },
        { name: "client", title: "Client" }
      ];
    },
    users() {
      return Users(7);
    }
  },
  template: `
    <k-users-view
      :loading="loading"
      :roles="roles"
      :users="users"
    />
  `
});

export const loading = () => ({
  extends: regular(),
  computed: {
    loading() {
      return true;
    }
  }
});

export const empty = () => ({
  extends: regular(),
  computed: {
    users() {
      return [];
    }
  }
});

export const roleFilter = () => ({
  extends: regular(),
  template: `
    <k-users-view
      :roles="roles"
      role="editor"
    />
  `,
});

