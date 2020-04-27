import Padding from "../storybook/Padding.js";

export default {
  title: "UI | Data / Async Collection",
  decorators: [Padding]
};

export const regular = () => ({
  computed: {
    load() {
      return async ({ page, limit }) => {

        return {
          data: [...Array(limit).keys()].map(item => {

            const id = item + ((page - 1) * limit) + 1;

            return {
              title: "List item no. " + id,
              info: "List item info",
              link: "https://getkirby.com",
              image: {
                url: "https://source.unsplash.com/user/erondu/1600x900?" + id
              },
              options: [
                { icon: "edit", text: "Edit", click: "edit" },
                { icon: "trash", text: "Delete", click: "delete" }
              ]
            };
          }),
          pagination: {
            total: 230
          }
        };

      };
    }
  },
  template: `
    <k-async-collection
      :load="load"
    />
  `,
});
