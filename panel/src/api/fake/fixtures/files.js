import { blueprint } from "./blueprints.js";

const file = (parentType, parentId, filename, merge = {}) => {
  const bp = merge.blueprint || blueprint("image");

  return {
    content: {
      caption: "Caption this!",
      copyright: "2020"
    },
    dimensions: {
      width: 1400,
      height: 930,
      orientation: "landscape"
    },
    extension: filename.split(".").pop(),
    filename: filename,
    id: parentType + "/" + parentId + "/" + filename,
    options: bp.options,
    parentId: parentId,
    mime: "image/jpeg",
    name: filename.split(".").slice(0, -1).join('.'),
    niceSize: "453.75 kB",
    url: "https://source.unsplash.com/user/erondu/1600x900",
    ...merge,
    blueprint: bp,
  };
}


export default [
  file("pages", "photography+animals", "abba.jpg"),
  file("pages", "photography+animals", "bird-reynolds.jpg"),
  file("pages", "photography+animals", "dumbo.jpg"),
  file("pages", "photography+animals", "free-wheely.jpg"),
  file("pages", "photography+animals", "peter-fox.jpg"),
  file("pages", "photography+animals", "steve-turtle.jpg"),
  file("pages", "photography+sky", "blood-moon.jpg"),
  file("pages", "photography+sky", "coconut-milkyway.jpg"),
  file("pages", "photography+sky", "dark-forest.jpg"),
];
