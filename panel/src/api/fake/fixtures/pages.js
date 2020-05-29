import { blueprint } from "./blueprints.js";

const page = (id, merge) => {
  const slug = id.split("+").pop();
  const blueprint = merge.blueprint || blueprint("default");

  return {
    id: id,
    blueprints: [],
    childIds: [],
    errors: [],
    hasChildren: false,
    hasFiles: false,
    num: null,
    options: blueprint.options,
    parents: [],
    previewUrl: "https://demo.getkirby.com/" + id,
    slug: slug,
    status: "listed",
    template: blueprint.name,
    title: slug,
    ...merge,
    blueprint: blueprint
  };
}

export default [
  page("photography", {
    blueprint: blueprint("photography"),
    childIds: ["photography+animals"],
    hasChildren: true,
    num: 1,
    title: "Photography",
  }),
  page("photography+animals", {
    blueprints: [],
    blueprint: blueprint("album"),
    errors: [],
    fileIds: ["pages/photography+animals/free-wheely.jpg"],
    hasFiles: true,
    num: 1,
    parentId: "photography",
    parents: [
      {
        title: "Photography",
        id: "photography"
      }
    ],
    title: "Animals",
  }),
  page("notes", {
    blueprint: blueprint("notes"),
    childIds: ["notes+through-the-desert"],
    hasChildren: true,
    num: 2,
    title: "Notes",
  }),
  page("notes+through-the-desert", {
    blueprint: blueprint("note"),
    content: {
      text: "Hello world",
      date: "2012-12-12",
      tags: ["nature", "landscape", "desert"],
    },
    num: 20121212,
    parents: [
      {
        title: "Notes",
        id: "notes"
      }
    ],
    title: "Through the desert",
  }),
];
