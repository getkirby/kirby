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
        filename: "example.jpg",
        height: 900,
        mime: "image/jpeg",
        niceSize: "128 KB",
        orientation: "landscape",
        template: "cover",
        url: "https://source.unsplash.com/user/erondu/1600x900",
        width: 1600,
      },
      tabs: [
        { name: "main", label: "Main" },
        { name: "seo", label: "SEO" },
      ],
    };
  },
  template: `
    <k-file-view
      :columns="columns"
      :file="file"
      :tabs="tabs"
      tab="main"
    />
  `
});
