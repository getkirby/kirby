<template>
  <nav v-if="show" :data-align="align" class="k-pagination">
    <k-button
      v-if="show"
      :disabled="!hasPrev"
      :tooltip="prevLabel"
      icon="angle-left"
      @click="prev"
    />

    <template v-if="details">
      <template v-if="dropdown">
        <k-dropdown>
          <k-button :disabled="!hasPages" class="k-pagination-details" @click="$refs.dropdown.toggle()">
            <template v-if="total > 1">
              {{ detailsText }}
            </template>{{ total }}
          </k-button>

          <k-dropdown-content
            ref="dropdown"
            class="k-pagination-selector"
            @open="$nextTick(() => $refs.page.focus())"
          >
            <div class="k-pagination-settings">
              <label for="k-pagination-page">
                <span>{{ pageLabel }}:</span>
                <select id="k-pagination-page" ref="page">
                  <option
                    v-for="p in pages"
                    :key="p"
                    :selected="page === p"
                    :value="p"
                  >
                    {{ p }}
                  </option>
                </select>
              </label>
              <k-button icon="check" @click="goTo($refs.page.value)" />
            </div>
          </k-dropdown-content>
        </k-dropdown>
      </template>
      <template v-else>
        <span class="k-pagination-details">
          <template v-if="total > 1">{{ detailsText }}</template>{{ total }}
        </span>
      </template>
    </template>

    <k-button
      v-if="show"
      :disabled="!hasNext"
      :tooltip="nextLabel"
      icon="angle-right"
      @click="next"
    />
  </nav>
</template>

<script>
/**
 * @example <k-pagination
 *   align="center"
 *   :details="true"
 *   :page="5"
 *   :total="125"
 *   :limit="10" />
 */
export default {
  props: {
    /**
     * The align prop makes it possible to move the pagination component according to the wrapper component.
     * @values left, centre, right
     */
    align: {
      type: String,
      default: "left"
    },
    /**
     * Show/hide the details display with the page selector in the center of the two navigation buttons.
     */
    details: {
      type: Boolean,
      default: false
    },
    dropdown: {
      type: Boolean,
      default: true
    },
    /**
     * Enable/disable keyboard navigation
     */
    keys: {
      type: Boolean,
      default: false
    },
    /**
     * Sets the limit of items to be shown per page
     */
    limit: {
      type: Number,
      default: 10
    },
    /**
     * Sets the current page
     */
    page: {
      type: Number,
      default: 1
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
     * Sets the total number of items that are in the paginated list. This has to be set higher to 0 to activate pagination.
     */
    total: {
      type: Number,
      default: 0
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
    },
    validate: {
      type: Function,
      default() {
        return Promise.resolve();
      }
    },
  },
  data() {
    return {
      currentPage: this.page
    };
  },
  computed: {
    show() {
      return this.pages > 1;
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
      if (this.limit === 1) {
        return this.start + " / ";
      } else {
        return this.start + "-" + this.end + " / ";
      }
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
    /**
     * Jump to the given page
     * @public
     */
    goTo(page) {
      this.validate(page)
        .then(() => {
          if (page < 1) {
            page = 1;
          }

          if (page > this.pages) {
            page = this.pages;
          }

          this.currentPage = page;

          if (this.$refs.dropdown) {
            this.$refs.dropdown.close();
          }

          this.$emit("paginate", {
            page: this.currentPage,
            start: this.start,
            end: this.end,
            limit: this.limit,
            offset: this.offset
          });
        })
        .catch(() => {
          // pagination stopped
        });
    },
    /**
     * Jump to the previous page
     * @public
     */
    prev() {
      this.goTo(this.currentPage - 1);
    },
    /**
     * Jump to the next page
     * @public
     */
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

<style>
.k-pagination {
  user-select: none;
  direction: ltr;
}
.k-pagination .k-button {
  padding: 1rem;
}
.k-pagination-details {
  white-space: nowrap;
}
.k-pagination > span {
  font-size: var(--text-sm);
}
.k-pagination[data-align="center"] {
  text-align: center;
}
.k-pagination[data-align="right"] {
  text-align: right;
}

.k-dropdown-content.k-pagination-selector {
  position: absolute;
  top: 100%;
  left: 50%;
  transform: translateX(-50%);
  background: var(--color-black);
}
[dir="ltr"] .k-dropdown-content.k-pagination-selector {
  direction: ltr;
}
[dir="rtl"] .k-dropdown-content.k-pagination-selector {
  direction: rtl;
}

.k-pagination-settings {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.k-pagination-settings .k-button {
  line-height: 1;
}
.k-pagination-settings label {
  display: flex;
  border-right: 1px solid rgba(255, 255, 255, .35);
  align-items: center;
  padding: .625rem 1rem;
  font-size: var(--text-xs);
}
.k-pagination-settings label span {
  margin-right: .5rem;
}
</style>
