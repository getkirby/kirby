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
            <template v-if="total > 1">{{ detailsText }}</template>{{ total }}
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
export default {
  props: {
    align: {
      type: String,
      default: "left"
    },
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
    page: {
      type: Number,
      default: 1
    },
    total: {
      type: Number,
      default: 0
    },
    limit: {
      type: Number,
      default: 10
    },
    keys: {
      type: Boolean,
      default: false
    },
    pageLabel: {
      type: String,
      default() {
        return this.$t("pagination.page");
      }
    },
    prevLabel: {
      type: String,
      default() {
        return this.$t("prev");
      }
    },
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
  font-size: $font-size-small;
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
  background: $color-black;

  [dir="ltr"] & {
    direction: ltr;
  }

  [dir="rtl"] & {
    direction: rtl;
  }
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
  border-right: 1px solid rgba(#fff, .35);
  align-items: center;
  padding: .625rem 1rem;
  font-size: $font-size-tiny;
}
.k-pagination-settings label span {
  margin-right: .5rem;
}
</style>
