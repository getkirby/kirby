export default {
  title: "App | Views / User"
};

export const regular = () => ({
  data() {
    return {
      columns: [
        {
          width: "1/1",
          sections: {
            fields: {
              type: "fields",
              fields: {
                twitter: {
                  type: "text",
                  width: "1/2"
                },
                github: {
                  type: "text",
                  width: "1/2"
                }
              },
            },
          },
        },
      ],
      user: {
        id: "ada",
        role: "admin",
        email: "ada@getkirby.com",
        name: "Ada Lovelace",
        avatar: {
          url: 'https://source.unsplash.com/user/erondu/400x400'
        },
        role: {
          title: "Editor"
        },
        language: "de"
      },
      tabs: [
        { name: "main", label: "Main" },
        { name: "profile", label: "Profile" },
      ],
    };
  },
  template: `
    <k-user-view
      :columns="columns"
      :user="user"
      :tabs="tabs"
      tab="main"
    />
  `
});
