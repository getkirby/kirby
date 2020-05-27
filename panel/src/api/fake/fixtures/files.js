const options = (merge) => {
  return {
    changeName: true,
    replace: true,
    delete: true,
    ...merge,
  };
};

export default [
  {
    dimensions: {
      width: 1400,
      height: 930,
      orientation: "landscape"
    },
    extension: "jpg",
    filename: "free-wheely.jpg",
    id: "pages/photography+animals/free-wheely.jpg",
    options: options(),
    parentId: "photography+animals",
    mime: "image/jpeg",
    name: "free-wheely",
    niceSize: "453.75 kB",
    template: "image",
    url: "https://source.unsplash.com/user/erondu/1600x900",
  },
];
