<template>
  <div class="kirby-search" @click="close">
    <div class="kirby-search-box" @click.stop>
      <div class="kirby-search-input">
        <input
          ref="input"
          type="text"
          v-model="query"
          @input="search"
          @keydown.down.prevent="down"
          @keydown.up.prevent="up"
          @keydown.tab.prevent="tab"
          @keydown.enter="enter"
          @keydown.esc="close"
        >
        <kirby-button icon="cancel" @click="close" />
      </div>
      <ul>
        <li v-for="(page, pageIndex) in pages" :key="page.id" :data-selected="selected === pageIndex">
          <kirby-link :to="'/pages/' + page.id" @click="click(pageIndex)">
            <strong>{{ page.title }}</strong>
            <small>{{ page.id }}</small>
          </kirby-link>
          <kirby-button v-if="page.hasChildren" icon="angle-right" @click="query = page.id + '/'; search()" />
        </li>
      </ul>
      <div class="kirby-search-empty" v-if="pages.length === 0">
        No Supages
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      pages: [],
      query: (this.$route.params.path || '') + '/',
      selected: -1
    }
  },
  watch: {
    $route() {
      this.query = (this.$route.params.path || '') + '/';
    }
  },
  mounted() {
    this.search();
    this.$refs.input.select();
  },
  methods: {
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
        this.query = page.id + "/";
        this.search();
        this.navigate(page);
      }
    },
    navigate(page) {
      this.$router.push("/pages/" + page.id);
      this.$store.dispatch("search", false);
    },
    search() {

      let parent = this.query.toLowerCase().split('/').slice(0, -1).join('/');
      let query  = this.query.toLowerCase().split('/').slice(-1)[0];
      let data   = null;

      if (query) {
        data = {
          filterBy: [
            {
              field: "uid",
              operator: "*=",
              value: query
            },
          ]
        };
      }

      this.$api.page.search(parent, data).then(response => {
        this.pages = response.data;
        this.selected = -1;
      }).catch(e => {
        this.pages = [];
        this.selected = -1;
      });
    },
    tab() {
      const page = this.pages[this.selected];

      if (page) {
        this.query = page.id + '/';
        this.search();

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
.kirby-search li {
  background: #fff;
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
.kirby-search li[data-selected] a {
  outline: 2px solid $color-focus;
  background: $color-focus-outline;
}
.kirby-search-empty {
  padding: .825rem .75rem;
  font-size: $font-size-tiny;
  background: $color-background;
  border-top: 1px dashed $color-border;
  color: $color-dark-grey;
}

</style>
