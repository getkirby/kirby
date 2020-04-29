import items from "./Items.js";

export default (limit, start = 1) => {

  return items(limit, start).map((item) => {
    item.title = "image-" + item.id + ".jpg",
    item.info = "3200 x 1400",

    item.options = [
      { icon: "open", text: "Open", click: "open" },
      "-",
      { icon: "title", text: "Rename", click: "rename" },
      { icon: "upload", text: "Replace", click: "replace" },
      "-",
      { icon: "trash", text: "Delete", click: "delete" },
    ];


    return item;
  });

};

