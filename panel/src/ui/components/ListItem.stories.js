import ListItem from "./ListItem.vue";

export default {
  title: "Items / List Item",
  component: ListItem
};

export const regular = () => ({
  props: {
    text: {
      default: "Item Text",
    },
    info: {
      default: "Item info",
    },
    link: {
      default: "https://getkirby.com"
    },
    image: {
      default() {
        return {
          url: "https://source.unsplash.com/user/erondu/1600x900",
        };
      }
    },
    options: {
      default() {
        return [
          { icon: "edit", text: "Edit" },
          { icon: "trash", text: "Delete" }
        ];
      }
    }
  },
  template: `
    <k-list-item v-bind="$props" />
  `,
});



