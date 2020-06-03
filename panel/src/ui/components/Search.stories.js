import Search from "./Search.vue";
import Padding from "../../../storybook/theme/Padding.js";
import { action } from "@storybook/addon-actions";

export default {
  title: "UI | Interaction / Search",
  decorators: [Padding],
  component: Search
};

export const regular = () => ({
  data() {
    return {
      type: "pages"
    };
  },
  computed: {
    delay() {
      return 0;
    },
    types() {
      const delay = this.delay;
      return {
        pages: {
          label: "Pages",
          icon: "page",
          search() {
            return async ({ query, limit }) => {

              if (query.length === 0) {
                return [];
              }

              await new Promise(r => setTimeout(r, delay));

              return [
                {
                  id: "explore",
                  title: "Explore the universe",
                  link: "/explore",
                  info: "explore"
                },
                {
                  id: "chasing",
                  title: "Chasing waterfalls",
                  link: "/chasing",
                  info: "chasing"
                },
                {
                  id: "himalaya",
                  title: "Himalaya and back",
                  link: "/himalaya",
                  info: "himalaya"
                }
              ];

            };
          }
        },
        files: {
          label: "Files",
          icon: "image",
          search(q) {
            return async () => {
              return [];
            }
          }
        },
        users: {
          label: "Users",
          icon: "users",
          search(q) {
            return async () => {
              return [];
            }
          }
        }
      };
    }
  },
  template: `
    <div>
      <k-button icon="search" @click="$refs.search.open()">
        Open search
      </k-button>
      <k-search ref="search" :types="types" :type="type" />
    </div>
  `
});

export const slowResponse = () => ({
  extends: regular(),
  computed: {
    delay() {
      return 2500;
    }
  }
});
