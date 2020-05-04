<template>
  <!-- Error -->
  <k-error-items
    v-if="error"
    :loader="loader"
    :layout="layout"
    :limit="loader.limit || pagination.limit"
  >
    {{ error }}
  </k-error-items>

  <!-- Collection -->
  <k-collection
    v-else
    v-bind="collection"
    v-on="listeners"
  />
</template>

<script>
export default {
  props: {
    empty: [String, Object],
    /**
     * Help text to be displayed below the collection in grey.
     */
    help: [Boolean, String],
    image: {
      type: [Object, Boolean],
      default: true,
    },
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
  data() {
    return {
      data: [],
      loading: true,
      loadingTimeout: false,
      limit: this.pagination.limit || 20,
      page: this.pagination.page || 1,
      total: 0,
      error: null
    };
  },
  computed: {
    collection() {
      return {
        empty: this.empty,
        help: this.help,
        image: this.image,
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
  watch: {
    items: {
      handler() {
        return this.load();
      },
      immediate: true
    }
  },
  methods: {
    startLoading() {
      clearTimeout(this.loadingTimeout);
      this.loadingTimeout = setTimeout(() => {
        this.loading = true;
      }, 250);
    },
    stopLoading() {
      clearTimeout(this.loadingTimeout);
      this.loading = false;
    },
    async load() {
      this.startLoading();

      try {
        const result = await this.items({ page: this.page, limit: this.limit });

        if (result.data) {
          this.total = result.pagination.total;
          this.data  = result.data;
        } else if (Array.isArray(result) === true) {
          this.total = result.length;
          this.data  = result;
        } else {
          this.total = 0;
          this.data  = [];
        }

      } catch (error) {
        this.error = error;
        console.error(error);
      }

      this.stopLoading();
    },
    reload() {
      this.error = null;
      this.load();
    }
  }
};
</script>
