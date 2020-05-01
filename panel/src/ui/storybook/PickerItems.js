
export const Items = () => ({
  data() {
    return {
      value: ["13", "72"],
      pagination: {
        page: 1,
        limit: 12
      }
    }
  },
  computed: {
    items() {
      return async (ids) => {
        return ids.map(this.item);
      };
    },
    model() {
      return "Item";
    },
    options() {
      return async ({page, limit, parent, search}) => {
        const total = 230;

        let data = [...Array(total).keys()].map(number => {
          let id = parent ? parent.id + "-" : null;
          id += number + 1;
          return this.item(id);
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
    },
    parents() {
      return false;
    }
  },
  methods: {
    item(id) {
      let item = {
        id: id.toString(),
        title: this.model + " no. " + id,
        info: this.model + " info",
        link: "https://getkirby.com",
        image: {
          url: "https://source.unsplash.com/user/erondu/400x225?" + id
        }
      };

      if (this.parents) {
        item.options= [{ icon: "angle-right", text: "Open"}];
      }

      return item;
    },
    onPaginate(pagination) {
      this.pagination = pagination;
    }
  }
});

export const Pages = () => ({
  extends: Items(),
  data() {
    return {
      value: ["14", "28", "53"]
    }
  },
  computed: {
    model() {
      return "Page";
    },
    parents() {
      return true;
    }
  }
});

export const Files = () => ({
  extends: Items(),
  computed: {
    model() {
      return "File";
    }
  }
});

export const Users = () => ({
  extends: Items(),
  computed: {
    model() {
      return "User";
    }
  }
});
