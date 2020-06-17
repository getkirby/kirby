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
      filename: "free-wheely.jpg",
      parent: {
        guid: "pages/photography+animals"
      },
      options: this.$model.files.dropdown({
        changeName: true,
        replace: true,
        delete: true
      }),
      preview: {
        height: 800,
        image: "https://source.unsplash.com/user/erondu/1600x900",
        mime: "image/jpeg",
        size: "128 KB",
        orientation: "landscape",
        template: "cover",
        width: 1600,
        link: "https://source.unsplash.com/user/erondu/1600x900",
        linkText: "/user/erondu/1600x900"
      },
      tab: "main",
      tabs: [
        { name: "main", label: "Main" },
        { name: "seo", label: "SEO" },
      ],
      value: {
        alt: "A really nice image"
      },
      view: "site",
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
      :filename="filename"
      :options="options"
      :preview="preview"
      :tab="tab"
      :tabs="tabs"
      :value="value"
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
