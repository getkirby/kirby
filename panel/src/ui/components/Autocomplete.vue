<template>
  <k-dropdown class="k-autocomplete">
    <!-- @slot Slot for the input element -->
    <slot />
    <k-dropdown-content
      ref="dropdown"
      :autofocus="true"
      v-on="$listeners"
    >
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
  </k-dropdown>
</template>

<script>

export default {
  props: {
    /**
     * Options for the autocomplete dropdown must be passed as an array
     * of objects. Each object can have as many items as you like,
     * but a `text` item is required to match agains the query:
     * ```
     * [{ text: "this will be searched", id: "anything else is optional" }];
     * ```
     */
    options: {
      type: Array,
      default() {
        return [];
      }
    },
    /**
     * Maximum number of entries in autocomplete dropdown
     */
    limit: {
      type: Number,
      default: 10,
    },
    /**
     * You can pass an array of strings, which should be ignored in the search.
     */
    skip: {
      type: Array,
      default() {
        return [];
      }
    }
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

      /**
       * Whenever the dropdown is being closed,
       * the event is fired
       */
      this.$emit("close");
    },
    onSelect(item) {
      this.close();

      /**
       * The event is being triggered as soon as one of
       * the options in the dropdown is being clicked or
       * enter/tab is being hit. Passes the selected item.
       */
      this.$emit("select", item);
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

      /**
       * On every keystroke this event is fired,
       * which can be used to react on searches.
       * Passes object with `query` and `matches` keys.
       */
      this.$emit("search", { query:query, matches: this.matches });
      this.$refs.dropdown.open();
    }
  }
}
</script>
