<template>
  <k-collection
    v-bind="collection"
    v-on="listeners"
  />
</template>

<script>
export default {
  props: {
    /**
     * Help text to be displayed below the collection in grey.
     */
    help: String,
    items: {
      type: Function,
      default() {
        return async ({ page, limit }) => {
          return [];
        };
      }
    },
    /**
     * Available options: `list`|`cardlets`|`cards`
     */
    layout: {
      type: String,
      default: "list"
    },
    /**
     * Settings for the empty loading state.
     * See EmptyItems for available options
     */
    loader: {
      type: Object,
      default() {
        return {}
      }
    },
    /**
     * Allow manual sorting via drag-and-drop
     */
    sortable: Boolean,
    pagination: {
      type: [Boolean, Object],
      default() {
        return {};
      }
    }
  },
  watch: {
    items: {
      handler() {
        return this.load();
      },
      immediate: true
    }
  },
  data() {
    return {
      data: [],
      loading: true,
      loadingTimeout: false,
      limit: 10,
      page: 1,
      total: 0
    };
  },
  computed: {
    collection() {
      return {
        help: this.help,
        items: this.data,
        layout: this.layout,
        loader: this.loader,
        loading: this.loading,
        pagination: {
          ...this.pagination,
          page: this.page,
          limit: this.limit,
          total: this.total
        },
        sortable: this.sortable
      };
    },
    listeners() {
      return {
        ...this.$listeners,
        paginate: (pagination) => {
          this.page  = pagination.page;
          this.limit = pagination.limit;
          this.$emit("paginate", pagination);
          this.load();
        }
      };
    }
  },
  methods: {
    startLoading() {
      clearTimeout(this.loadingTimeout);
      this.loadingTimeout = setTimeout(() => {
        this.loading = true;
      }, 150);
    },
    stopLoading() {
      clearTimeout(this.loadingTimeout);
      this.loading = false;
    },
    load() {
      this.startLoading();
      this
        .items({ page: this.page, limit: this.limit })
        .then(result => {
          this.total   = result.pagination.total;
          this.data    = result.data;
          this.stopLoading();
        })
    },
    reload() {
      this.data = [];
      this.load();
    }
  }
};
</script>
