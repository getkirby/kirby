export default {
  inheritAttrs: false,
  props: {
    blueprint: String,
    column: String,
    parent: String,
    name: String,
    timestamp: Number
  },
  data() {
    return {
      data: [],
      error: null,
      isLoading: false,
      isProcessing: false,
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
    paginationId() {
      return "kirby$pagination$" + this.parent + "/" + this.name;
    }
  },
  watch: {
    // Reload the section when
    // the view has changed in the backend
    timestamp() {
      this.reload();
    }
  },
  methods: {
    items(data) {
      return data;
    },
    async load(reload) {
      if (!reload) {
        this.isLoading = true;
      }

      this.isProcessing = true;

      if (this.pagination.page === null) {
        this.pagination.page = localStorage.getItem(this.paginationId) || 1;
      }

      try {
        const response = await this.$api.get(
          this.parent + "/sections/" + this.name,
          { page: this.pagination.page }
        );

        this.options = response.options;
        this.pagination = response.pagination;
        this.data = this.items(response.data);
      } catch (error) {
        this.error = error.message;
      } finally {
        this.isProcessing = false;
        this.isLoading = false;
      }
    },
    paginate(pagination) {
      localStorage.setItem(this.paginationId, pagination.page);
      this.pagination = pagination;
      this.reload();
    },
    async reload() {
      await this.load(true);
    }
  }
};
