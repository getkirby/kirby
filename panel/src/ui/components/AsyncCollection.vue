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
import items from "@/ui/mixins/items.js";

export default {
  mixins: [items],
  props: {
    /**
     * Help text to be displayed below the collection in grey.
     */
    help: [Boolean, String],
    items: {
      type: Function,
      default() {
        return async ({ page, limit }) => {
          return [];
        };
      }
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
        size: this.size,
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
      this.$emit("startLoading");
    },
    stopLoading() {
      clearTimeout(this.loadingTimeout);
      this.loading = false;
      this.$emit("stopLoading");
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

        this.map();

      } catch (error) {
        this.error = error;
        console.error(error);
      }

      this.stopLoading();
    },
    map() {},
    reload() {
      this.error = null;
      this.load();
    }
  }
};
</script>
