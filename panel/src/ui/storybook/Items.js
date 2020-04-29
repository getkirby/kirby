export default (limit, start = 1) => {
  return [...Array(limit).keys()].map(item => {
    return {
      title: "List item no. " + (item + start),
      info: "List item info",
      link: "https://getkirby.com",
      image: {
        url: "https://source.unsplash.com/user/erondu/400x225?" + (item + start)
      },
      options: [
        { icon: "edit", text: "Edit", click: "edit" },
        { icon: "trash", text: "Delete", click: "delete" }
      ]
    };
  });
};

