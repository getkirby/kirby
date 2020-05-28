export default {
  title: "App | Views / User"
};

export const regular = () => ({
  data() {
    return {
      breadcrumb: [
        {
          label: "Ada Lovelace",
          link: "/users/ada",
        }
      ],
      columns: [
        {
          width: "1/1",
          sections: {
            fields: {
              type: "fields",
              fields: {
                twitter: {
                  type: "text",
                  width: "1/2",
                },
                github: {
                  type: "text",
                  width: "1/2",
                },
              },
            },
          },
        },
      ],
      options: this.$model.users.dropdown({
        changeName: true,
        changeEmail: true,
        changeRole: true,
        changePassword: true,
        changeLanguage: true,
        delete: true
      }),
      tabs: [
        { name: "main", label: "Main" },
        { name: "profile", label: "Profile" },
      ],
      user: {
        id: "ada",
        role: "admin",
        email: "ada@getkirby.com",
        name: "Ada Lovelace",
        avatar: {
          url: "https://source.unsplash.com/user/erondu/400x400",
        },
        role: {
          title: "Editor",
        },
        language: "de",
      },
    };
  },
  computed: {
    lock() {
      return false;
    }
  },
  template: `
    <k-user-view
      :breadcrumb="breadcrumb"
      :columns="columns"
      :lock="lock"
      :options="options"
      :tabs="tabs"
      :user="user"
      tab="main"
    />
  `
});

export const locked = () => ({
  extends: regular(),
  computed: {
    lock() {
      return {
        email: "ada@getkirby.com"
      };
    },
  },
});
