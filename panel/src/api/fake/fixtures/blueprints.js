export default [
  {
    title: "Album",
    id: "pages+album",
    name: "album",
  },
  {
    title: "Note",
    id: "pages+note",
    name: "note",
  },
  {
    title: "Photography",
    id: "pages+photography",
    name: "photography",
    status: {
      draft: {
        label: "Draft",
        text: "The page is in draft mode and only visible for logged in editors",
      },
      unlisted: {
        label: "Unlisted",
        text: "The page is only accessible via URL",
      },
      listed: {
        label: "Public",
        text: "The page is public for anyone",
      }
    },
    options: {
      changeStatus: true
    }
  },
];
