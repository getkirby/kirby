
export default {
  "pages+note+content": {
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
    icon: "text",
    label: "Content",
    name: "main",
  },
  "pages+note+seo": {
    icon: "search",
    label: "SEO",
    name: "seo",
  },
};
