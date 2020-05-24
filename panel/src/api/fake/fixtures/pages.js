export default [
  {
    id: "photography",
    blueprintId: "pages/photography",
    blueprints: [],
    /** TODO: do this smarter/dynamic? */
    blueprint: {
      options: {},
      status: {
        draft: {
          text: "Draft",
          icon: "circle-outline",
          color: "red-light"
        },
        unlisted: {
          text: "Unlisted",
          icon: "circle-half",
          color: "blue-light"
        },
        listed: {
          text: "Listed",
          icon: "circle",
          color: "green-light"
        }
      }
    },
    childIds: [
      "photography+animals",
    ],
    errors: [],
    hasChildren: true,
    hasFiles: false,
    num: 1,
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
    errors: [],
    fileIds: [
      "free-wheely.jpg"
    ],
    hasChildren: true,
    hasFiles: false,
    num: 1,
    parents: [],
    slug: "animals",
    status: "listed",
    template: "album",
    title: "Animals",
  }
];
