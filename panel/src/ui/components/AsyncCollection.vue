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
    load: {
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
    load: {
      handler() {
        return this.fetch();
      },
      immediate: true
    }
  },
  data() {
    return {
      items: [],
      limit: 10,
      page: 1,
      total: 0
    };
  },
  computed: {
    collection() {
      return {
        items: this.items,
        pagination: {
          page: this.page,
          limit: this.limit,
          total: this.total
        }
      };
    },
    listeners() {
      return {
        ...this.$listeners,
        paginate: (pagination) => {
          this.page  = pagination.page;
          this.limit = pagination.limit;
          this.fetch();
        }
      };
    }
  },
  methods: {
    fetch() {
      this
        .load({ page: this.page, limit: this.limit })
        .then(result => {
          this.total = result.pagination.total;
          this.items = result.data;
        })
    }
  }
};
</script>

