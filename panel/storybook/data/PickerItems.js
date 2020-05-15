
export const Item = (id, model = "Item") =>  {
  return {
    id: id.toString(),
    title: model + " no. " + id,
    info: model + " info",
    link: "https://getkirby.com",
    image: {
      url: "https://source.unsplash.com/user/erondu/400x225?" + id
    }
  };
};

export const Options = async (page, limit, parent, search, model) => {
  const total = 215;

  let data = [...Array(total).keys()].map(number => {
    let id = number +1;
    id = parent ? parent.id + "-" + id : id;
    return model(id);
  });

  if (search) {
    data = data.filter(option => option.title.includes(search));
  }

  const offset = (page - 1) * limit;
  data = data.slice(offset, offset + limit);

  return {
    data: data,
    pagination: {
      total: total
    }
  };
};

export const Page = (id) => {
  let item = Item(id, "Page");
  item.options= [{ icon: "angle-right", text: "Open"}];
  return item;
};

export const Pages = (page, limit, parent, search) => {
  return Options(page, limit, parent, search, Page);
};

export const File = (id) => {
  return Item(id, "File");
};

export const Files = (page, limit, parent, search) => {
  return Options(page, limit, parent, search, File);
};

export const User = (id) => {
  return Item(id, "User");
};

export const Users = (page, limit, parent, search) => {
  return Options(page, limit, parent, search, User);
};
