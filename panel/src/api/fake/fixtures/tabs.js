
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
  "files+image+meta": {
    columns: [
      {
        width: "2/3",
        sections: {
          fields: {
            type: "fields",
            fields: {
              caption: {
                label: "Caption",
                type: "text"
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
              copyright: {
                label: "Copyright",
                type: "text",
              }
            },
          },
        },
      },
    ],
    icon: "image",
    label: "Meta",
    name: "main",
  },
  "files+image+seo": {
    icon: "search",
    label: "SEO",
    name: "seo",
  },
  "users+admin+profile": {
    columns: [
      {
        width: "1/1",
        sections: {
          fields: {
            type: "fields",
            fields: {
              twitter: {
                label: "Twitter",
                type: "text",
                before: "@"
              },
            },
          },
        },
      }
    ],
    icon: "user",
    label: "Profile",
    name: "profile",
  },
  "site+main": {
    columns: [
      {
        width: "1/2",
        sections: {
          photography: {
            add: true,
            pages: async () => Pages(10),
            type: "pages",
            layout: "cards",
            preview: {
              ratio: "3/2",
              cover: true,
            },
          },
        },
      },
      {
        width: "1/2",
        sections: {
          notes: {
            add: true,
            pages: async () => Pages(7),
            type: "pages",
          },
          pages: {
            add: true,
            pages: async () => Pages(4),
            type: "pages",
          },
        },
      },
    ],
    icon: "text",
    name: "main",
    label: "Dashboard"
  }
};
