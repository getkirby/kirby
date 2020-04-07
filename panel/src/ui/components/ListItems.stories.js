import ListItems from "./ListItems.vue";

export default {
  title: "Items / List Items",
  component: ListItems
};

export const regular = () => ({
  computed: {
    items() {
      return [...Array(20).keys()].map(item => {
        return {
          text: "List item no. " + item,
          info: "List item info",
          link: "https://getkirby.com",
          image: {
            url: "https://source.unsplash.com/user/erondu/1600x900"
          },
          options: [
            { icon: "edit", text: "Edit" },
            { icon: "trash", text: "Delete" }
          ]
        };
      });
    }
  },
  template: `
    <k-list-items :items="items" />
  `,
});



