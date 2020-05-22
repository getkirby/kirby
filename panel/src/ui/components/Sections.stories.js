import Sections from "./Sections.vue";
import Padding from "../../../storybook/theme/Padding.js";
import Files from "../../../storybook/data/Files.js";
import Pages from "../../../storybook/data/Pages.js";
import { action } from "@storybook/addon-actions";

export default {
  title: "UI | Blueprints / Sections",
  decorators: [Padding],
  component: Sections
};

export const pages = () => ({
  computed: {
    columns() {
      return [
        {
          width: "1/1",
          sections: {
            drafts: {
              label: "Drafts",
              type: "pages",
              add: true,
              pages: async () => {
                return Pages(2).map(page => {
                  page.flag.icon.type = "circle-outline";
                  page.flag.icon.color = "red-light";
                  return page;
                });
              }
            },
            published: {
              label: "Published",
              layout: "cards",
              type: "pages",
              preview: {
                ratio: "3/2"
              },
              pages: async () => {
                return Pages(10);
              }
            }
          }
        }
      ];
    }
  },
  template: `
    <k-sections :columns="columns" />
  `
});

export const notes = () => ({
  extends: pages(),
  computed: {
    columns() {
      return [
        {
          width: "1/2",
          sections: {
            drafts: {
              label: "Drafts",
              type: "pages",
              add: true,
              pages: async () => {
                return Pages(2).map(page => {
                  page.flag.icon.type = "circle-outline";
                  page.flag.icon.color = "red-light";
                  return page;
                });
              }
            },
            inReview: {
              label: "In review",
              type: "pages",
              pages: async () => {
                return Pages(5).map(page => {
                  page.flag.icon.type = "circle-half";
                  page.flag.icon.color = "blue-light";
                  return page;
                });
              }
            }
          }
        },
        {
          width: "1/2",
          sections: {
            published: {
              label: "Published",
              type: "pages",
              limit: 20,
              pages: async () => {
                return Pages(20);
              }
            }
          }
        }
      ];
    }
  },
});


export const note = () => ({
  extends: pages(),
  computed: {
    columns() {
      return [
        {
          width: "2/3",
          sections: {
            content: {
              type: "fields",
              fields: {
                heading: {
                  type: "text"
                },
                text: {
                  type: "textarea",
                  size: "large"
                }
              }
            }
          }
        },
        {
          width: "1/3",
          sections: {
            meta: {
              type: "fields",
              fields: {
                date: {
                  type: "date"
                },
                tags: {
                  type: "tags"
                }
              }
            },
            images: {
              type: "files",
              files: async () => {
                return Files(5)
              }
            }
          }
        }
      ];
    }
  },
});
