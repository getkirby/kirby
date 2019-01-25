<template>
  <div class="k-search" role="search" @click="close">
    <div class="k-search-box" @click.stop>
      <div class="k-search-input">
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
        <k-button :tooltip="$t('close')" icon="cancel" @click="close" />
      </div>
      <ul>
        <li
          v-for="(page, pageIndex) in pages"
          :key="page.id"
          :data-selected="selected === pageIndex"
          @mouseover="selected = pageIndex"
        >
          <k-link :to="$api.pages.link(page.id)" @click="click(pageIndex)">
            <strong>{{ page.title }}</strong>
            <small>{{ page.id }}</small>
          </k-link>
        </li>
      </ul>
    </div>
  </div>
</template>

<script>

import debounce from "@/helpers/debounce.js";
import config from "@/config/config.js";

export default {
  data() {
    return {
      pages: [],
      q: null,
      selected: -1
    }
  },
  watch: {
    q: debounce(function (q) {
      this.search(q);
    }, 200)
  },
  mounted() {
    this.$nextTick(() => {
      this.$refs.input.focus();
    });
  },
  methods: {
    open(event) {
      event.preventDefault();
      this.$store.dispatch("search", true);
    },
    click(index) {
      this.selected = index;
      this.tab();
    },
    close() {
      this.$store.dispatch("search", false);
    },
    down() {
      if (this.selected < this.pages.length - 1) {
        this.selected++;
      }
    },
    enter() {
      let page = this.pages[this.selected] || this.pages[0];

      if (page) {
        this.navigate(page);
      }
    },
    navigate(page) {
      this.$router.push(this.$api.pages.link(page.id));
      this.close();
    },
    search(query) {
      this.$api.get('site/search', { q: query, limit: config.search.limit }).then(response => {
        this.pages = response.data;
        this.selected = -1;
      }).catch(() => {
        this.pages = [];
        this.selected = -1;
      });
    },
    tab() {
      const page = this.pages[this.selected];

      if (page) {
        this.navigate(page);
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
  position: fixed;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  z-index: 1000;
  overflow: auto;
  background: $color-backdrop;
}
.k-search-box {
  max-width: 30rem;
  margin: 0 auto;
  box-shadow: $box-shadow;

  @media screen and (min-width: $breakpoint-medium) {
    margin: 2.5rem auto;
  }
}
.k-search-input {
  background: #efefef;
  display: flex;
}
.k-search-input input {
  background: none;
  flex-grow: 1;
  font: inherit;
  padding: .75rem;
  border: 0;
  height: 2.5rem;
}
.k-search-input .k-button {
  width: 2.5rem;
  line-height: 1;
}
.k-search input:focus {
  outline: 0;
}
.k-search ul {
  background: #fff;
}
.k-search li {
  border-bottom: 1px solid $color-background;
  line-height: 1.125;
  display: flex;
}
.k-search li .k-link {
  display: block;
  padding: .5rem .75rem;
  flex-grow: 1;
}
.k-search li strong {
  display: block;
  font-size: $font-size-small;
  font-weight: 400;
}
.k-search li small {
  font-size: $font-size-tiny;
  color: $color-dark-grey;
}
.k-search li[data-selected] {
  outline: 2px solid $color-focus;
  background: $color-focus-outline;
  border-bottom: 1px solid transparent;
}
.k-search-empty {
  padding: .825rem .75rem;
  font-size: $font-size-tiny;
  background: $color-background;
  border-top: 1px dashed $color-border;
  color: $color-dark-grey;
}

</style>
