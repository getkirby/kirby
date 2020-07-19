<template>
  <div class="k-search" role="search" @click="close">
    <div class="k-search-box" @click.stop>
      <div class="k-search-input">
        <k-dropdown class="k-search-types">
          <k-button :icon="type.icon" @click="$refs.types.toggle()">{{ type.label }}:</k-button>
          <k-dropdown-content ref="types">
            <k-dropdown-item
              v-for="(type, typeIndex) in types"
              :key="typeIndex"
              :icon="type.icon"
              @click="currentType = typeIndex"
            >
              {{ type.label }}
            </k-dropdown-item>
          </k-dropdown-content>
        </k-dropdown>
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
          icon="cancel"
          @click="close"
        />
      </div>
      <ul>
        <li
          v-for="(item, itemIndex) in items"
          :key="item.id"
          :data-selected="selected === itemIndex"
          @mouseover="selected = itemIndex"
        >
          <k-link :to="item.link" @click="close">
            <strong>{{ item.title }}</strong>
            <small>{{ item.info }}</small>
          </k-link>
        </li>
      </ul>
    </div>
  </div>
</template>

<script>
import config from "@/config/config.js";
import debounce from "@/helpers/debounce.js";

export default {
  data() {
    return {
      items: [],
      q: null,
      selected: -1,
      currentType: this.$store.state.view === "users" ? "users" : "pages"
    }
  },
  computed: {
    type() {
      return this.types[this.currentType] || this.types["pages"];
    },
    types() {
      return {
        pages: {
          label: this.$t("pages"),
          icon: "page",
          endpoint: "site/search"
        },
        files: {
          label: this.$t("files"),
          icon: "image",
          endpoint: "files/search"
        },
        users: {
          label: this.$t("users"),
          icon: "users",
          endpoint: "users/search"
        }
      };
    }
  },
  watch: {
    q: debounce(function (q) {
      this.search(q);
    }, 200),
    currentType() {
      this.search(this.q);
    }
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
    map_files(item) {
      return {
        id: item.id,
        title: item.filename,
        link: item.link,
        info: item.id
      };
    },
    map_pages(item) {
      return {
        id: item.id,
        title: item.title,
        link: this.$api.pages.link(item.id),
        info: item.id
      };
    },
    map_users(item) {
      return {
        id: item.id,
        title: item.name,
        link: this.$api.users.link(item.id),
        info: item.email
      };
    },
    navigate(item) {
      this.$go(item.link);
      this.close();
    },
    async search(query) {
      try {
        const response = await this.$api.get(
          this.type.endpoint,
          { q: query, limit: config.search.limit }
        );
        this.items = response.data.map(this['map_' + this.currentType]);

      } catch (error) {
        this.items = [];

      } finally {
        this.selected = -1;
      }
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
.k-search-types {
  flex-shrink: 0;
  display: flex;
}
.k-search-types > .k-button {
  padding: 0 0 0 .7rem;
  font-size: $font-size-medium;
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
