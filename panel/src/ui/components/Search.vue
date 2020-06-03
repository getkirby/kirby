<template>
  <portal v-if="isOpen">
    <div
      :dir="$direction"
      :data-loading="isLoading"
      class="k-search fixed bg-backdrop"
      role="search"
      @click="close"
    >
      <div
        class="k-search-box shadow-xl bg-background rounded-sm"
        @click.stop
      >
        <div class="k-search-input flex">
          <!-- Types select -->
          <k-dropdown class="k-search-types flex">
            <k-button
              :text="currentType.label"
              :icon="currentType.icon"
              @click="$refs.types.toggle()"
            />
            <k-dropdown-content ref="types">
              <k-dropdown-item
                v-for="(searchType, searchTypeIndex) in types"
                :key="searchTypeIndex"
                :icon="searchType.icon"
                @click="onChangeType(searchTypeIndex)"
              >
                {{ searchType.label }}
              </k-dropdown-item>
            </k-dropdown-content>
          </k-dropdown>

          <!-- Input -->
          <input
            ref="input"
            v-model="q"
            :placeholder="$t('search') + ' …'"
            aria-label="$t('search')"
            type="text"
            @keydown.down.prevent="down"
            @keydown.up.prevent="up"
            @keydown.tab.prevent="tab"
            @keydown.enter="enter"
            @keydown.esc="close"
          >
          <k-button
            :tooltip="$t('close')"
            class="k-search-close"
            :icon="isLoading ? 'loader' : 'cancel'"
            @click="close"
          />
        </div>

        <ul>
          <!-- Results -->
          <li
            v-for="(item, itemIndex) in items"
            :key="item.id"
            :data-selected="selected === itemIndex"
            @mouseover="selected = itemIndex"
          >
            <k-link
              :to="item.link"
              @click="click(itemIndex)"
            >
              <strong>{{ item.title }}</strong>
              <small>{{ item.info }}</small>
            </k-link>
          </li>

          <!-- No results -->
          <li v-if="q && isLoaded && items.length < 1">
            <k-link :disabled="true">
              <strong>{{ $t("search.empty") }}</strong>
            </k-link>
          </li>
        </ul>
      </div>
    </div>
  </portal>
</template>

<script>
import debounce from "@/ui/helpers/debounce.js";

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
      currentType: this.getCurrentType(this.type),
      isOpen: false,
      isLoading: false,
      isLoaded: false,
      items: [],
      q: null,
      selected: -1
    }
  },
  watch: {
    currentType() {
      this.search(this.q);
    },
    q: debounce(function (q) {
      this.search(q);
    }, 200),
    type() {
      this.currentType = this.getCurrentType(this.type);
    },
  },
  methods: {
    click(index) {
      this.selected = index;
      this.close();
    },
    close() {
      this.isOpen = false;
      this.isLoaded = false;
      this.items = [];
      this.q = null;
    },
    down() {
      if (this.selected < this.items.length - 1) {
        this.selected++;
      }
    },
    enter() {
      let item = this.items[this.selected] || this.items[0];

      if (item) {
        this.navigate(item);
      }
    },
    getCurrentType(type) {
      return this.types[type] || this.types[Object.keys(this.types)[0]];
    },
    navigate(item) {
      this.$router.push(item.link);
      this.close();
    },
    onChangeType(type) {
      this.currentType = this.getCurrentType(type);
    },
    open(event) {
      this.isOpen = true;
      setTimeout(() => {
        this.$refs.input.focus();
      }, 1);
    },
    async search(query) {
      this.$refs.types.close();
      this.isLoaded  = false;
      this.isLoading = true;

      try {
        this.items = await this.currentType.search()({ query, limit: 10 });
      } catch (error) {
        this.items = [];
      }

      this.selected = -1;
      this.isLoading = false;
      this.isLoaded  = true;
    },
    tab() {
      const item = this.items[this.selected];

      if (item) {
        this.navigate(item);
      }
    },
    up() {
      if (this.selected >= 0) {
        this.selected--;
      }
    }
  }
};
</script>

<style lang="scss">
.k-search {
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  z-index: 1000;
  overflow: auto;
}
.k-search-box {
  max-width: 30rem;
  margin: 0 auto;

  @media screen and (min-width: $breakpoint-md) {
    margin: 2.5rem auto;
  }
}
.k-search-types {
  flex-shrink: 0;
}
.k-search-types > .k-button {
  padding: 0 0 0 .7rem;
  font-size: $text-base;
  line-height: 1;
  height: 2.5rem;

  .k-icon {
    height: 2.5rem;
  }
  .k-button-text {
    opacity: 1;
    font-weight: 500;
  }
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
  width: 2.5rem;
  line-height: 1;
}
.k-search input:focus {
  outline: 0;
}
.k-search .k-icon-loader {
  animation: Spin 2s linear infinite;
}
.k-search ul {
  background: $color-white;
  overflow: hidden;
  border-bottom-left-radius: $rounded-sm;
  border-bottom-right-radius: $rounded-sm;
}
.k-search li {
  border-bottom: 1px solid $color-background;
  line-height: 1.125;
  display: flex;
}
.k-search li:last-child {
  border-bottom: 0;
}
.k-search li .k-link {
  display: block;
  padding: .5rem .75rem;
  flex-grow: 1;
}
.k-search li strong {
  display: block;
  font-size: $text-sm;
  font-weight: 400;
}
.k-search li small {
  font-size: $text-xs;
  opacity: .75;
}
.k-search li[data-selected] {
  background: $color-black;
  border-bottom: 1px solid transparent;
  color: #fff;
}
.k-search-empty {
  padding: .825rem .75rem;
  font-size: $text-xs;
  background: $color-background;
  border-top: 1px dashed $color-border;
  color: $color-gray-700;
}
</style>
