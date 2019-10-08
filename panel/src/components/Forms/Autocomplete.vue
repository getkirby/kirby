<template>
  <k-dropdown class="k-autocomplete">
    <slot />
    <k-dropdown-content ref="dropdown" :autofocus="true" v-on="$listeners">
      <k-dropdown-item
        v-for="(item, index) in matches"
        :key="index"
        v-bind="item"
        @mousedown="onSelect(item)"
        @keydown.tab.prevent="onSelect(item)"
        @keydown.enter.prevent="onSelect(item)"
        @keydown.left.prevent="close"
        @keydown.backspace.prevent="close"
        @keydown.delete.prevent="close"
      >
        {{ item.text }}
      </k-dropdown-item>
    </k-dropdown-content>
    {{ query }}
  </k-dropdown>
</template>

<script>

export default {
  props: {
    limit: 10,
    skip: {
      type: Array,
      default() {
        return [];
      }
    },
    options: Array,
    query: String
  },
  data() {
    return {
      matches: [],
      selected: {text: null}
    };
  },
  methods: {
    close() {
      this.$refs.dropdown.close();
    },
    onSelect(value) {
      this.$refs.dropdown.close();
      this.$emit("select", value);
    },
    search(query) {

      if (query.length < 1) {
        return;
      }

      // Filter options by query to retrieve items (no more than this.limit)
      const regex = new RegExp(RegExp.escape(query), "ig");

      this.matches = this.options
        .filter(option => {

          // skip all options without valid text
          if (!option.text) {
            return false;
          }

          // skip all options in the skip array
          if (this.skip.indexOf(option.value) !== -1) {
            return false;
          }

          // match the search with the text
          return option.text.match(regex) !== null;

        })
        .slice(0, this.limit);

      this.$emit("search", query, this.matches);
      this.$refs.dropdown.open();
    }
  }
}
</script>

<style lang="scss">

</style>
