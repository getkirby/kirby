const blueprints = {
  "pages/album": {
    title: "Album",
    id: "pages/album",
    name: "album"
  },
  "pages/note": {
    title: "Note",
    id: "pages/note",
    name: "note"
  },
  "pages/photography": {
    title: "Photography",
    id: "pages/photograhy",
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

};

export default {
  get: {
    "blueprints": () => ([
      blueprints["pages/album"],
      blueprints["pages/note"],
      blueprints["pages/photography"],
    ]),
    "blueprints/pages/album": () => {
      return blueprints["pages/album"];
    },
    "blueprints/pages/note": () => {
      return blueprints["pages/note"];
    },
    "blueprints/pages/photography": () => {
      return blueprints["pages/photography"];
    }
  }
};
