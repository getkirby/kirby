<template>
  <nav
    v-if="show"
    class="k-pagination"
  >
    <k-button
      v-if="show"
      :disabled="!hasPrev"
      :tooltip="prevLabel"
      class="k-pagination-button"
      icon="angle-left"
      @click="prev"
    />
    <template v-if="details">
      <template v-if="dropdown">
        <k-page-dropdown
          :page="currentPage"
          :page-label="pageLabel"
          :pages="pages"
          :text="detailsText"
          class="k-pagination-details"
          @change="goTo($event)"
        />
      </template>
      <template v-else>
        <span class="k-pagination-details text-sm">{{ detailsText }}</span>
      </template>
    </template>

    <k-button
      v-if="show"
      :disabled="!hasNext"
      :tooltip="nextLabel"
      class="k-pagination-button"
      icon="angle-right"
      @click="next"
    />
  </nav>
</template>

<script>
export default {
  props: {
    /**
     * Show/hide the details display with the page selector
     * in the center of the two navigation buttons.
     */
    details: {
      type: Boolean,
      default: false
    },
    dropdown: {
      type: Boolean,
      default: true
    },
    validate: {
      type: Function,
      default() {
        return Promise.resolve();
      }
    },
    /**
     * Sets the current page
     */
    page: {
      type: Number,
      default: 1
    },
    /**
     * Sets the total number of items that are in the paginated list.
     * This has to be set higher than `0` to activate pagination.
     */
    total: {
      type: Number,
      default: 0
    },
    /**
     * Sets the limit of items to be shown per page.
     */
    limit: {
      type: Number,
      default: 10
    },
    /**
     * Enable/disable keyboard navigation
     */
    keys: {
      type: Boolean,
      default: false
    },
    /**
     * Sets the label for the page selector
     */
    pageLabel: {
      type: String,
      default() {
        return this.$t("pagination.page");
      }
    },
    /**
     * Sets the label for the `prev` arrow button
     */
    prevLabel: {
      type: String,
      default() {
        return this.$t("prev");
      }
    },
    /**
     * Sets the label for the `next` arrow button
     */
    nextLabel: {
      type: String,
      default() {
        return this.$t("next");
      }
    }
  },
  data() {
    return {
      currentPage: this.page
    };
  },
  computed: {
    show() {
      return this.limit > 0 && this.pages > 1;
    },
    start() {
      return (this.currentPage - 1) * this.limit + 1;
    },
    end() {
      let value = this.start - 1 + this.limit;

      if (value > this.total) {
        return this.total;
      } else {
        return value;
      }
    },
    detailsText() {
      if (this.total <= 1) {
        return this.total;
      }

      if (this.limit === 1) {
        return this.start + " / " + this.total;
      }

      return this.start + "-" + this.end + " / " + this.total;
    },
    pages() {
      return Math.ceil(this.total / this.limit);
    },
    hasPrev() {
      return this.start > 1;
    },
    hasNext() {
      return this.end < this.total;
    },
    hasPages() {
      return this.total > this.limit;
    },
    offset() {
      return this.start - 1;
    }
  },
  watch: {
    page(page) {
      this.currentPage = parseInt(page);
    }
  },
  created() {
    if (this.keys === true) {
      window.addEventListener("keydown", this.navigate, false);
    }
  },
  destroyed() {
    window.removeEventListener("keydown", this.navigate, false);
  },
  methods: {
    async goTo(page) {
      try {
        await this.validate(page);

        if (page < 1) {
          page = 1;
        }

        if (page > this.pages) {
          page = this.pages;
        }

        this.currentPage = page;

        /**
         * Listening to the paginate event is the most straight
         * forward way to react to the pagination component. An object
         * with `page`, `start`, `end`, `limit` and `offset` items
         * is passed.
         */
        this.$emit("paginate", {
          page: this.currentPage,
          start: this.start,
          end: this.end,
          limit: this.limit,
          offset: this.offset
        });

      } catch (error) {
        // pagination stopped
      }
    },
    prev() {
      this.goTo(this.currentPage - 1);
    },
    next() {
      this.goTo(this.currentPage + 1);
    },
    navigate(e) {
      switch (e.code) {
        case "ArrowLeft":
          this.prev();
          break;
        case "ArrowRight":
          this.next();
          break;
      }
    }
  }
};
</script>

<style lang="scss">
.k-pagination {
  display: inline-flex;
  align-items: center;
  justify-content: space-between;
  user-select: none;
}
.k-pagination-details {
  white-space: nowrap;
  display: flex;
  align-items: center;
  height: 2.5rem;
  line-height: 1;
}
.k-pagination-details .k-page-dropdown-toggle {
  display: inline-flex;
  height: 2.5rem;
  padding: 0 .5rem;
}
.k-pagination-button {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 2.5rem;
  width: 2.5rem;
}
</style>
