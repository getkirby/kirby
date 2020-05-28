import UsersView from "./UsersView.vue";

export default {
  title: "App | Views / Users",
  component: UsersView
};

export const regular = () => ({
  computed: {
    roles() {
      return [
        { name: "admin", title: "Admin" },
        { name: "editor", title: "Editor" },
        { name: "client", title: "Client" }
      ];
    }
  },
  template: `
    <k-users-view :roles="roles" />
  `
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

