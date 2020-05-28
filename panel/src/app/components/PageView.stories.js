export default {
  title: "App | Views / Page"
};

export const regular = () => ({
  data() {
    return {
      breadcrumb: [
        {
          label: "Notes",
          link: "/pages/notes"
        },
        {
          label: "Through the desert",
          link: "/pages/notes+through-the-desert"
        }
      ],
      columns: [
        {
          width: "2/3",
          sections: {
            fields: {
              type: "fields",
              fields: {
                text: {
                  label: "Text",
                  type: "textarea",
                  size: "large",
                },
              },
            },
          },
        },
        {
          width: "1/3",
          sections: {
            fields: {
              type: "fields",
              fields: {
                date: {
                  label: "Date",
                  type: "date",
                },
                tags: {
                  label: "Tags",
                  type: "tags",
                },
              },
            },
          },
        },
      ],
      id: "notes+through-the-desert",
      options: this.$model.pages.dropdown({
        changeTitle: true,
        duplicate: true,
        changeSlug: true,
        changeStatus: true,
        changeTemplate: false,
        delete: true,
      }),
      preview: "https://demo.getkirby.com/notes/through-the-desert",
      status: {
        text: "Published",
        tooltip: "Published",
        icon: {
          type: "circle",
          color: "green-light",
          size: "small",
        },
      },
      tabs: [
        { icon: "text", name: "main", label: "Content" },
        { icon: "search", name: "seo", label: "SEO" },
      ],
      template: "Article",
      title: "Through the desert",
      value: {
        text: "Hello world",
        date: "2012-12-12",
        tags: ["travelling", "nature", "desert"]
      }
    };
  },
  computed: {
    lock() {
      return false;
    }
  },
  template: `
    <k-page-view
      :breadcrumb="breadcrumb"
      :columns="columns"
      :id="id"
      :lock="lock"
      :options="options"
      :preview="preview"
      :rename="true"
      :status="status"
      :tabs="tabs"
      :template="template"
      :title="title"
      :value="value"
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
