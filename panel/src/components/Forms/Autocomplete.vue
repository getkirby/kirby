<template>
  <k-dropdown class="k-autocomplete">
    <!-- @slot Use to insert your input -->
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
/**
 * The Autocomplete component can be wrapped around any form of input to get an flexible starting point to provide an real-time autocomplete dropdown. We use it for our `TagsInput` component.
 */
export default {
  props: {
    /**
     * Maximum number of displayed results
     */
    limit: {
      type: Number,
      default: 10
    },
    /**
     * You can pass an array of strings, which should be ignored in the search.
     */
    skip: {
      type: Array,
      default() {
        return [];
      }
    },
    /**
     * Options for the autocomplete dropdown must be passed as an array of 
     * objects. Each object can have as many items as you like, but a text 
     * item is required to match agains the query
     * 
     * @example [ { text: "this will be searched", id: "anything else is optional" }, ];
     */
    options: Array,
    /**
     * Term to filter options
     */
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
    onSelect(item) {
      /**
       * New value has been selected
       * @event select
       * @property {object} item - option item
       */
      this.$emit("select", item);
      this.$refs.dropdown.close();
    },
    /**
     * Opens the dropdown and filters the options
     * @public
     * @param {string} query search term
     */
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
       * Search has been performed
       * @event search
       * @property {string} query
       * @property {array} matches - all options that match the search query
       */
      this.$emit("search", query, this.matches);
      this.$refs.dropdown.open();
    }
  }
}
</script>
