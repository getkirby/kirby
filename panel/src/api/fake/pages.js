import blueprints from "./blueprints.js";

const photographyPage = {
  blueprint: blueprints.get["blueprints/pages/photography"](),
  blueprints: [],
  errors: [],
  hasChildren: true,
  hasFiles: false,
  num: 1,
  parents: [],
  slug: "photography",
  status: "listed",
  template: "photography",
  title: "Photography",
};

const animalsPage = {
  blueprint: blueprints.get["blueprints/pages/album"](),
  blueprints: [
    blueprints.get["blueprints/pages/album"](),
    blueprints.get["blueprints/pages/note"](),
  ],
  errors: [],
  hasChildren: false,
  hasFiles: true,
  num: 2,
  parent: photographyPage,
  parents: [
    photographyPage,
  ],
  slug: "animals",
  status: "listed",
  template: "album",
  title: "Animals",
};

export default {
  get: {
    "pages/photography": () => (photographyPage),
    "pages/photograhy+animals": () => (animalsPage),
    "pages/photography+animals/files/peacock.jpg": () => ({
      extension: "jpg",
      filename: "peacock.jpg",
      height: 900,
      mime: "image/jpeg",
      name: "peacock",
      niceSize: "127 KB",
      parent: animalsPage,
      template: "cover",
      width: 1600,
      url: "https://source.unsplash.com/user/erondu/1600x900"
    })
  }
};
