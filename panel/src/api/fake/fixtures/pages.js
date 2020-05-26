const options = (merge) => {
  return {
    changeSlug: true,
    changeStatus: true,
    changeTemplate: true,
    changeTitle: true,
    delete: true,
    duplicate: true,
    preview: true,
    ...merge
  };
};

const blueprint = (merge) => {
  return {
    options: options(),
    num: "default",
    status: {
      draft: {
        text: "Draft",
        icon: "circle-outline",
        color: "red-light",
      },
      unlisted: {
        text: "Unlisted",
        icon: "circle-half",
        color: "blue-light",
      },
      listed: {
        text: "Listed",
        icon: "circle",
        color: "green-light",
      },
    },
    ...merge
  };
};

export default [
  {
    id: "photography",
    blueprintId: "pages/photography",
    blueprints: [],
    /** TODO: do this smarter/dynamic? */
    blueprint: blueprint(),
    childIds: ["photography+animals"],
    errors: [],
    hasChildren: true,
    hasFiles: false,
    num: 1,
    options: options(),
    parents: [],
    slug: "photography",
    status: "listed",
    template: "photography",
    title: "Photography",
  },
  {
    id: "photography+animals",
    blueprintId: "pages/album",
    blueprints: [],
    blueprint: blueprint(),
    errors: [],
    fileIds: ["pages/photography+animals/free-wheely.jpg"],
    hasChildren: true,
    hasFiles: false,
    num: 1,
    options: options(),
    parentId: "photography",
    parents: [],
    slug: "animals",
    status: "listed",
    template: "album",
    title: "Animals",
  },
];
