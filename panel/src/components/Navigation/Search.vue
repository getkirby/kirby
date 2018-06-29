<template>
  <div class="kirby-search" @click="close">
    <div class="kirby-search-box" @click.stop>
      <div class="kirby-search-input">
        <input
          ref="input"
          v-model="q"
          type="text"
          @input="search(q)"
          @keydown.down.prevent="down"
          @keydown.up.prevent="up"
          @keydown.tab.prevent="tab"
          @keydown.enter="enter"
          @keydown.esc="close"
          @keydown.meta.left.prevent="back"
          @keydown.meta.right.prevent="tab"
        >
        <kirby-button v-show="parent.length" icon="parent" @click="back" />
        <kirby-button icon="cancel" @click="close" />
      </div>
      <ul>
        <li
          v-for="(page, pageIndex) in pages"
          :key="page.id"
          :data-selected="selected === pageIndex"
          @mouseover="selected = pageIndex"
        >
          <kirby-link :to="'/pages/' + page.id.replace('/', '+')" @click="click(pageIndex)">
            <strong>{{ page.title }}</strong>
            <small>{{ page.id }}</small>
          </kirby-link>
          <kirby-button v-if="page.hasChildren" icon="angle-right" @click="selected = pageIndex; tab()" />
        </li>
      </ul>
      <div v-if="pages.length === 0" class="kirby-search-empty">
        {{ $t('search.noSubpages') }}
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      pages: [],
      parent: '',
      path: '',
      q: '',
      query: null,
      selected: -1
    }
  },
  mounted() {

    if (this.$route.params.path) {
      this.search(this.$route.params.path + '/');
    } else {
      this.search();
    }

    // put the query into the input
    this.q = '/' + this.path;

    const start = this.parent.lastIndexOf('/') + 2;
    const end   = this.q.length;

    this.$nextTick(() => {
      if (this.$refs.input.setSelectionRange) {
        this.$refs.input.setSelectionRange(start, end);
        this.$refs.input.focus();
      } else {
        this.$refs.input.select();
      }
    });

  },
  methods: {
    open(event) {
      event.preventDefault();
      this.$store.dispatch("search", true);
    },
    parse(path) {
      this.path   = String(path || '').replace('+', '/');

      const parts = this.path.toLowerCase().replace(/^\//, '').split('/');

      this.parent = '';
      this.query  = '';

      if (parts.length === 1) {
        this.query = parts[0];
      } else if (parts.length > 1) {
        this.parent = parts.slice(0, -1).join('/');
        this.query  = parts.slice(-1)[0];
      }

    },
    back() {
      const parent = this.q.replace(/\/$/, '').split('/').slice(0, -1).join('/');

      this.q = parent + '/';
      this.search(this.q);
      this.$refs.input.focus();
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
        this.search(page.id);
        this.navigate(page);
      }
    },
    navigate(page) {
      this.$router.push("/pages/" + page.id.replace('/', '+'));
      this.$store.dispatch("search", false);
    },
    search(path) {

      this.parse(path);

      let data = null;

      if (this.query.length) {
        data = {
          filterBy: [
            {
              field: "uid",
              operator: "*=",
              value: this.query
            },
          ]
        };
      }

      this.$api.page.search(this.parent, data).then(response => {
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
        this.q = '/' + page.id + '/';
        this.search(this.q);

        if (page.hasChildren === false) {
          this.navigate(page);
        }
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
.kirby-search {
  position: fixed;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  z-index: 1000;
  overflow: auto;
  background: $color-backdrop;
  backdrop-filter: blur(2px);
}
.kirby-search-box {
  max-width: 30rem;
  margin: 5rem auto;
  box-shadow: $box-shadow;
}
.kirby-search-input {
  background: #efefef;
  display: flex;
}
.kirby-search-input input {
  background: none;
  flex-grow: 1;
  text-transform: lowercase;
  font: inherit;
  padding: .75rem;
  border: 0;
}
.kirby-search-input .kirby-button {
  width: 2.5rem;
}
.kirby-search input:focus {
  outline: 0;
}
.kirby-search ul {
  background: #fff;
}
.kirby-search li {
  border-bottom: 1px solid $color-background;
  line-height: 1.125;
  display: flex;
}
.kirby-search li .kirby-link {
  display: block;
  padding: .5rem .75rem;
  flex-grow: 1;
}
.kirby-search li .kirby-button {
  width: 2.5rem;
}
.kirby-search li strong {
  display: block;
  font-size: $font-size-small;
  font-weight: 400;
}
.kirby-search li small {
  font-size: $font-size-tiny;
  color: $color-dark-grey;
}
.kirby-search li[data-selected] {
  outline: 2px solid $color-focus;
  background: $color-focus-outline;
  border-bottom: 1px solid transparent;
}
.kirby-search-empty {
  padding: .825rem .75rem;
  font-size: $font-size-tiny;
  background: $color-background;
  border-top: 1px dashed $color-border;
  color: $color-dark-grey;
}

</style>
