<template>
  <k-overlay ref="overlay">
    <div class="k-search" role="search">
      <div class="k-search-input">
        <!-- Type select -->
        <k-dropdown class="k-search-types">
          <k-button
            :icon="currentType.icon"
            :text="currentType.label"
            @click="$refs.types.toggle()"
          />
          <k-dropdown-content ref="types">
            <k-dropdown-item
              v-for="(typeItem, typeIndex) in types"
              :key="typeIndex"
              :icon="typeItem.icon"
              @click="changeType(typeIndex)"
            >
              {{ typeItem.label }}
            </k-dropdown-item>
          </k-dropdown-content>
        </k-dropdown>

        <!-- Input -->
        <input
          ref="input"
          v-model="q"
          :placeholder="$t('search') + ' …'"
          :aria-label="$t('search')"
          :autofocus="true"
          type="text"
          @input="hasResults = true"
          @keydown.down.prevent="onDown"
          @keydown.up.prevent="onUp"
          @keydown.tab.prevent="onTab"
          @keydown.enter="onEnter"
          @keydown.esc="close"
        />
        <k-button
          :icon="isLoading ? 'loader' : 'cancel'"
          :tooltip="$t('close')"
          class="k-search-close"
          @click="close"
        />
      </div>

      <div v-if="q && (!hasResults || items.length)" class="k-search-results">
        <!-- Results -->
        <k-items
          v-if="items.length"
          ref="items"
          :items="items"
          @hover="onHover"
          @mouseout.native="select(-1)"
        />

        <!-- No results -->
        <p v-else-if="!hasResults" class="k-search-empty">
          {{ $t("search.results.none") }}
        </p>
      </div>
    </div>
  </k-overlay>
</template>

<script>
import debounce from "@/helpers/debounce.js";

export default {
  props: {
    types: {
      type: Object,
      default() {
        return {};
      }
    },
    type: String
  },
  data() {
    return {
      isLoading: false,
      hasResults: true,
      items: [],
      currentType: this.getType(this.type),
      q: null,
      selected: -1
    };
  },
  watch: {
    q(newQuery, oldQuery) {
      if (newQuery !== oldQuery) {
        this.search(this.q);
      }
    },
    currentType(newType, oldType) {
      if (newType !== oldType) {
        this.search(this.q);
      }
    },
    type() {
      this.currentType = this.getType(this.type);
    }
  },
  created() {
    this.search = debounce(this.search, 250);
    this.$events.$on("keydown.cmd.shift.f", this.open);
  },
  destroyed() {
    this.$events.$off("keydown.cmd.shift.f", this.open);
  },
  methods: {
    changeType(type) {
      this.currentType = this.getType(type);
      this.$nextTick(() => {
        this.$refs.input.focus();
      });
    },
    close() {
      this.$refs.overlay.close();
      this.hasResults = true;
      this.items = [];
      this.q = null;
    },
    getType(type) {
      return this.types[type] || this.types[Object.keys(this.types)[0]];
    },
    navigate(item) {
      this.$go(item.link);
      this.close();
    },
    onDown() {
      if (this.selected < this.items.length - 1) {
        this.select(this.selected + 1);
      }
    },
    onEnter() {
      let item = this.items[this.selected] || this.items[0];

      if (item) {
        this.navigate(item);
      }
    },
    onHover(e, icon, index) {
      this.select(index);
    },
    onTab() {
      const item = this.items[this.selected];

      if (item) {
        this.navigate(item);
      }
    },
    onUp() {
      if (this.selected >= 0) {
        this.select(this.selected - 1);
      }
    },
    open() {
      this.$refs.overlay.open();
    },
    async search(query) {
      this.isLoading = true;

      if (this.$refs.types) {
        this.$refs.types.close();
      }

      try {
        // Skip API call if query empty
        if (query === null || query === "") {
          throw Error("Empty query");
        }

        const response = await this.$search(this.currentType.id, query);

        if (response === false) {
          throw Error("JSON parsing failed");
        }

        this.items = response.results;
      } catch (error) {
        this.items = [];
      } finally {
        this.select(-1);
        this.isLoading = false;
        this.hasResults = this.items.length > 0;
      }
    },
    select(index) {
      this.selected = index;
      if (this.$refs.items) {
        const items = this.$refs.items.$el.querySelectorAll(".k-item");
        [...items].forEach((item) => delete item.dataset.selected);
        if (index >= 0) {
          items[index].dataset.selected = true;
        }
      }
    }
  }
};
</script>

<style>
.k-search {
  max-width: 30rem;
  margin: 2.5rem auto;
  box-shadow: var(--shadow-lg);
}
.k-search-input {
  background: var(--color-light);
  display: flex;
}
.k-search-types {
  flex-shrink: 0;
  display: flex;
}
.k-search-types > .k-button {
  padding-inline-start: 1rem;
  font-size: var(--text-base);
  line-height: 1;
  height: 2.5rem;
}
.k-search-types > .k-button .k-icon {
  height: 2.5rem;
}
.k-search-types > .k-button .k-button-text {
  opacity: 1;
  font-weight: 500;
}
.k-search-input input {
  background: none;
  flex-grow: 1;
  font: inherit;
  padding: 0.75rem;
  border: 0;
  height: 2.5rem;
}
.k-search-close {
  width: 3rem;
  line-height: 1;
}
.k-search-close .k-icon-loader {
  animation: Spin 2s linear infinite;
}
.k-search input:focus {
  outline: 0;
}

.k-search-results {
  padding: 0.5rem 1rem 1rem;
  background: var(--color-light);
}
.k-search .k-item:not(:last-child) {
  margin-bottom: 0.25rem;
}
.k-search .k-item[data-selected="true"] {
  outline: 2px solid var(--color-focus);
}
.k-search .k-item-info {
  font-size: var(--text-xs);
}

.k-search-empty {
  text-align: center;
  font-size: var(--text-xs);
  color: var(--color-gray-600);
}
</style>
