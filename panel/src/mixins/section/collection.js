export default {
  inheritAttrs: false,
  props: {
    blueprint: String,
    column: String,
    parent: String,
    name: String,
  },
  data() {
    return {
      data: [],
      error: null,
      isLoading: false,
      options: {
        empty: null,
        headline: null,
        help: null,
        layout: "list",
        link: null,
        max: null,
        min: null,
        size: null,
        sortable: null
      },
      pagination: {
        page: null
      }
    };
  },
  computed: {
    headline() {
      return this.options.headline || " ";
    },
    help() {
      return this.options.help;
    },
    isInvalid() {
      if (this.options.min && this.data.length < this.options.min) {
        return true;
      }
      
      if (this.options.max && this.data.length > this.options.max) {
        return true;
      }
      
      return false;
    },
    language() {
      return this.$store.state.languages.current;
    },
    paginationId() {
      return "kirby$pagination$" + this.parent + "/" + this.name;
    }
  },
  watch: {
    language() {
      this.reload();
    }
  },
  methods: {
    items(data) {
      return data;
    },
    load(reload) {
      if (!reload) {
        this.isLoading = true;
      }

      if (this.pagination.page === null) {
        this.pagination.page = localStorage.getItem(this.paginationId) || 1;
      }

      this.$api
        .get(this.parent + "/sections/" + this.name, {
          page: this.pagination.page
        })
        .then(response => {
          this.isLoading = false;
          this.options = response.options;
          this.pagination = response.pagination;
          this.data = this.items(response.data);
        })
        .catch(error => {
          this.isLoading = false;
          this.error = error.message;
        });
    },
    paginate(pagination) {
      localStorage.setItem(this.paginationId, pagination.page);
      this.pagination = pagination;
      this.reload();
    },
    reload() {
      this.load(true);
    }
  }
};
