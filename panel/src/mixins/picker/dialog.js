import debounce from "@/helpers/debounce.js";

export default {
  data() {
    return {
      models: [],
      issue: null,
      selected: {},
      options: {
        endpoint: null,
        max: null,
        multiple: true,
        parent: null,
        selected: [],
        search: true
      },
      search: null,
      pagination: {
        limit: 20,
        page: 1,
        total: 0
      }
    }
  },
  computed: {
    multiple() {
      return this.options.multiple === true && this.options.max !== 1;
    },
    checkedIcon() {
      return this.multiple === true ? "check" : "circle-filled";
    }
  },
  watch: {
    search: debounce(function () {
      this.pagination.page = 0;
      this.fetch();
    }, 200),
  },
  methods: {
    fetch() {
      const params = {
        page: this.pagination.page,
        search: this.search,
        ...this.fetchData || {}
      };

      return this.$api
        .get(this.options.endpoint, params)
        .then(response => {
          this.models     = response.data;
          this.pagination = response.pagination;

          if (this.onFetched) {
            this.onFetched(response);
          }
        })
        .catch(e => {
          this.models = [];
          this.issue  = e.message;
        });
    },
    open(models, options) {

      // reset pagination
      this.pagination.page = 0;

      // reset the search
      this.search = null;

      let fetch = true;

      if (Array.isArray(models)) {
        this.models = models;
        fetch       = false;
      } else {
        this.models = [];
        options     = models;
      }

      this.options = {
        ...this.options,
        ...options
      };

      this.selected = {};

      this.options.selected.forEach(id => {
        this.$set(this.selected, id, {
          id: id
        });
      });

      if (fetch) {
        this.fetch().then(() => {
          this.$refs.dialog.open();
        });
      } else {
        this.$refs.dialog.open();
      }
    },
    paginate(pagination) {
      this.pagination.page  = pagination.page;
      this.pagination.limit = pagination.limit;
      this.fetch();
    },
    submit() {
      this.$emit("submit", Object.values(this.selected));
      this.$refs.dialog.close();
    },
    isSelected(item) {
      return this.selected[item.id] !== undefined;
    },
    toggle(item) {
      if (this.options.multiple === false || this.options.max === 1) {
        this.selected = {};
      }

      if (this.isSelected(item) === true) {
        this.$delete(this.selected, item.id);
        return;
      }

      if (this.options.max && this.options.max <= Object.keys(this.selected).length) {
        return;
      }

      this.$set(this.selected, item.id, item);
    }
  }
};
