import items from "./Items.js";

export default (limit, start = 1) => {

  return items(limit, start).map((item) => {
    item.title = "Page no. " + item.id,
    item.info = "Page info",
    item.flag = {
      icon: {
        type: "circle",
        color: "green-light",
        size: "small"
      },
    };

    item.options = [
      { icon: "open", text: "Open", click: "open" },
      "-",
      { icon: "title", text: "Rename", click: "rename" },
      { icon: "copy", text: "Duplicate", click: "duplicate" },
      "-",
      { icon: "url", text: "Change URL", click: "changeUrl" },
      { icon: "preview", text: "Change status", click: "changeStatus" },
      "-",
      { icon: "trash", text: "Delete", click: "remove" },
    ];


    return item;
  });

};
