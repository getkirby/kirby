import tabs from "./tabs.js";

const status = (merge) => {
  return {
    draft: {
      color: "red-light",
      icon: "circle-outline",
      label: "Draft",
      text: "The page is in draft mode and only visible for logged in editors",
    },
    unlisted: {
      color: "blue-light",
      icon: "circle-half",
      label: "Unlisted",
      text: "The page is only accessible via URL",
    },
    listed: {
      color: "green-light",
      icon: "circle",
      label: "Published",
      text: "The page is public for anyone",
    },
    ...merge,
  };
};

const pageOptions = (merge) => {
  return {
    changeSlug: true,
    changeStatus: true,
    changeTemplate: true,
    changeTitle: true,
    delete: true,
    duplicate: true,
    preview: true,
    ...merge,
  };
};

const blueprints = [
  {
    id: "pages+default",
    name: "default",
    options: pageOptions(),
    status: status(),
    title: "Default",
  },
  {
    id: "pages+album",
    name: "album",
    options: pageOptions(),
    status: status(),
    title: "Album",
  },
  {
    id: "pages+note",
    name: "note",
    options: pageOptions(),
    status: status(),
    title: "Note",
    tabs: [
      tabs["pages+note+content"],
      tabs["pages+note+seo"]
    ],
  },
  {
    id: "pages+photography",
    name: "photography",
    options: pageOptions(),
    status: status(),
    title: "Photography",
  },
  {
    id: "files+image",
    name: "image",
    title: "Image",
    tabs: [
      tabs["files+image+meta"],
      tabs["files+image+seo"]
    ],
  },
  {
    id: "users+admin",
    name: "admin",
    title: "Admin",
    tabs: [
      tabs["users+admin+profile"]
    ],
  },
  {
    id: "site",
    name: "site",
    title: "Site",
    tabs: [
      tabs["site+main"]
    ],
  },
];

export const blueprint = (name) => {
  return blueprints.filter(blueprint => blueprint.name === name)[0] || blueprints[0];
};

export default blueprints;
