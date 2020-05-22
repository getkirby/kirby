import FileRoute from "./FileRoute.vue";

export default {
  title: "App | Routes / File ",
  component: FileRoute
};

export const regular = () => ({
  template: `
    <k-file-route
      parent="pages/photography+animals"
      filename="free-wheely.jpg"
    />
  `
});
