import FileView from "./FileView.vue";

export default {
  title: "App | Views / File",
  component: FileView,
};

const altField = {
  label: "Alternative text",
  type: "text",
};

export const regular = () => ({
  data() {
    return {
      breadcrumb: [
        {
          label: "Photography",
          link: "/pages/photography"
        },
        {
          label: "Animals",
          link: "/pages/photography+animals"
        },
        {
          label: "free-wheely.jpg",
          link: "/pages/photography+animals/files/free-wheely.jpg"
        }
      ],
      columns: [
        {
          width: "1/1",
          sections: {
            fields: {
              type: "fields",
              fields: {
                alt: altField,
              },
            },
          },
        },
      ],
      file: {
        filename: "free-wheely.jpg",
        height: 900,
        mime: "image/jpeg",
        niceSize: "128 KB",
        orientation: "landscape",
        parent: {
          guid: "pages/photography+animals"
        },
        template: "cover",
        url: "https://source.unsplash.com/user/erondu/1600x900",
        width: 1600,
      },
      options: this.$model.files.dropdown({
        changeName: true,
        replace: true,
        delete: true
      }),
      tab: "main",
      tabs: [
        { name: "main", label: "Main" },
        { name: "seo", label: "SEO" },
      ],
      view: "site"
    };
  },
  computed: {
    lock() {
      return false;
    }
  },
  template: `
    <k-file-view
      :breadcrumb="breadcrumb"
      :columns="columns"
      :lock="lock"
      :file="file"
      :options="options"
      :tab="tab"
      :tabs="tabs"
      :view="view"
    />
  `
});


export const locked = () => ({
  extends: regular(),
  computed: {
    lock() {
      return {
        email: "ada@getkirby.com",
      };
    },
  },
});

export const siteFile = () => ({
  extends: regular(),
  data() {
    return {
      breadcrumb: [
        {
          label: "free-wheely.jpg",
          link: "/site/files/free-wheely.jpg"
        }
      ]
    };
  }
});

export const userFile = () => ({
  extends: regular(),
  data() {
    return {
      breadcrumb: [
        {
          label: "Ada Lovelace",
          link: "/users/ada"
        },
        {
          label: "free-wheely.jpg",
          link: "/users/ada/files/free-wheely.jpg",
        },
      ],
      view: "users"
    };
  },
});
