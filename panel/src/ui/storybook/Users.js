import items from "./Items.js";

export default (limit, start = 1) => {

  return items(limit, start).map((item) => {
    item.title = "User no. " + item.id,
    item.info  = "mail+" + item.id + "@getkirby.com",
    item.image.cover = true;

    item.options = [
      { icon: "title", text: "Rename this user", click: "rename" },
      "-",
      { icon: "email", text: "Change email", click: "email" },
      { icon: "bolt", text: "Change role", click: "role" },
      { icon: "key", text: "Change password", click: "password" },
      { icon: "globe", text: "Change language", click: "language" },
      "-",
      { icon: "trash", text: "Delete this user", click: "delete" },
    ];


    return item;
  });

};

