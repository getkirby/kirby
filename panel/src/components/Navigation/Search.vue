<template>
  <k-overlay ref="overlay">
    <div class="k-search" role="search">
      <div class="k-search-input">
        <!-- Type select -->
        <k-dropdown class="k-search-types">
          <k-button :icon="currentType.icon" @click="$refs.types.toggle()">
            {{ currentType.label }}:
          </k-button>
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
        >
        <k-button
          :icon="isLoading ? 'loader' : 'cancel'"
          :tooltip="$t('close')"
          class="k-search-close"
          @click="close"
        />
      </div>

      <div
        v-if="q && (!hasResults || items.length)"
        class="k-search-results"
      >
        <!-- Results -->
        <ul v-if="items.length" @mouseout="selected = -1">
          <li
            v-for="(item, itemIndex) in items"
            :key="item.id"
            :data-selected="selected === itemIndex"
            @mouseover="selected = itemIndex"
          >
            <k-link :to="item.link" @click="close">
              <span class="k-search-item-image">
                <k-image
                  v-if="imageOptions(item.image)"
                  v-bind="imageOptions(item.image)"
                />
                <k-icon
                  v-else
                  v-bind="item.icon"
                />
              </span>
              <span class="k-search-item-info">
                <strong>{{ item.title }}</strong>
                <small>{{ item.info }}</small>
              </span>
            </k-link>
          </li>
        </ul>

        <!-- No results -->
        <p v-else-if="!hasResults" class="k-search-empty">
          {{ $t("search.results.none") }}
        </p>
      </div>
    </div>
  </k-overlay>
</template>

<script>
import config from "@/config/config.js";
import debounce from "@/helpers/debounce.js";
import previewThumb from "@/helpers/previewThumb.js";

export default {
  props: {
    types: {
      type: Object,
      default() {
        return {};
      }
    },
    type: {
      type: String
    },
  },
  data() {
    return {
      isLoading: false,
      hasResults: true,
      items: [],
      currentType: this.getType(this.type),
      q: null,
      selected: -1,
    }
  },
  watch: {
    q() {
      this.search(this.q);
    },
    currentType() {
      this.search(this.q);
    },
    type() {
      this.currentType = this.getType(this.type);
    },
    types() {
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
    imageOptions(image) {
      return previewThumb(image);
    },
    navigate(item) {
      this.$go(item.link);
      this.close();
    },
    onDown() {
      if (this.selected < this.items.length - 1) {
        this.selected++;
      }
    },
    onEnter() {
      let item = this.items[this.selected] || this.items[0];

      if (item) {
        this.navigate(item);
      }
    },
    onTab() {
      const item = this.items[this.selected];

      if (item) {
        this.navigate(item);
      }
    },
    onUp() {
      if (this.selected >= 0) {
        this.selected--;
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
        if (query === "") {
          throw new Error;
        }

        this.items = await this.currentType.search({
          query: query,
          limit: config.search.limit
        });


      } catch (error) {
        this.items = [];

      } finally {
        this.selected   = -1;
        this.isLoading  = false;
        this.hasResults = this.items.length > 0;
      }
    }
  }
};
</script>

<style>
.k-search {
  max-width: 30rem;
  margin: 0 auto;
  box-shadow: var(--shadow-lg);
}
@media screen and (min-width: 65em) {
  .k-search {
    margin: 2.5rem auto;
  }
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
  padding: 0 0 0 1rem;
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
  padding: .75rem;
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
  padding: .5rem 1rem 1rem;
  background: var(--color-light);
}
.k-search li {
  background: var(--color-white);
  display: flex;
  box-shadow: var(--shadow);
}
.k-search li:not(:last-child) {
  margin-bottom: .25rem;
}
.k-search li[data-selected] {
  outline: 2px solid var(--color-focus);
}
.k-search li .k-link {
  display: flex;
  align-items: center;
  flex-grow: 1;
}
.k-search-item-image,
.k-search-item-image > * {
  height: 50px;
  width: 50px;
}

.k-search-item-info {
  padding: .5rem .75rem;
  line-height: 1.125;
}

.k-search li strong {
  display: block;
  font-size: var(--text-sm);
  font-weight: 400;
}
.k-search li small {
  font-size: var(--text-xs);
  color: var(--color-gray-600);
}

.k-search-empty {
  text-align: center;
  font-size: var(--text-xs);
  color: var(--color-gray-600);
}
</style>
