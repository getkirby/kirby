import FileView from "./FileView.vue";

export default {
  title: "App | Views / File"
};

export const regular = () => ({
  components: {
    "k-file-view": FileView
  },
  data() {
    return {
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
      blueprint: {
        tabs: [

        ]
      }
    };
  },
  template: `
    <k-file-view :file="file" />
  `
});
