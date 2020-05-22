export default (limit, start = 1) => {
  return [...Array(limit).keys()].map(item => {
    const id = item + start;

    return {
      id: id,
      title: "List item no. " + id,
      info: "List item info",
      link: "https://getkirby.com",
      preview: {
        image: "https://source.unsplash.com/user/erondu/400x225?" + id
      },
      options: [
        { icon: "edit", text: "Edit", click: "edit" },
        { icon: "trash", text: "Delete", click: "delete" }
      ]
    };
  });
};
