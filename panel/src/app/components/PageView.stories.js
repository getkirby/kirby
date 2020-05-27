import fixtures from "@/api/fake/fixtures/pages.js"

export default {
  title: "App | Views / Page"
};

export const regular = () => ({
  data() {
    return {
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
                  size: "medium"
                }
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
                name: {
                  label: "Location",
                  type: "text",
                },
                email: {
                  label: "Copyright",
                  type: "text",
                }
              },
            },
          },
        },
      ],
      page: fixtures[0],
      tabs: [
        { name: "main", label: "Main" },
        { name: "seo", label: "SEO" },
      ],
    };
  },
  template: `
    <k-page-view
      :columns="columns"
      :page="page"
      :tabs="tabs"
      tab="main"
    />
  `
});
