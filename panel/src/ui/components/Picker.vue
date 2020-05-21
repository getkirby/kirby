<template>
  <div class="k-picker">
    <!-- Search -->
    <k-input
      v-if="search"
      v-model="q"
      :autofocus="true"
      :placeholder="$t('search') + ' …'"
      type="text"
      icon="search"
      class="k-picker-search mb-4 py-2 px-4 rounded-sm"
    />

    <!-- Navigation -->
    <header
      v-if="parent"
      slot="navigation"
      class="k-picker-navbar mb-4"
    >
      <k-button
        :disabled="!parent.id"
        :tooltip="$t('back')"
        icon="angle-left"
        @click="onBack"
      >
        {{ parent.title }}
      </k-button>
    </header>

    <!-- Error -->
    <k-error-items
      v-if="error"
      :layout="layout"
      :limit="pagination.limit"
    >
      {{ error }}
    </k-error-items>

    <!-- Collection -->
    <k-collection
      v-else
      v-bind="collection"
      v-on="listeners"
    />
  </div>
</template>

<script>
import AsyncCollection from "@/ui/components/AsyncCollection.vue";
import debounce from "@/ui/helpers/debounce.js";

export default {
  extends: AsyncCollection,
  beforeCreate: function(){
    this.$delete(this.$options.props, "items");
    this.$delete(this.$options.props, "loader");
  },
  props: {
    /**
     * Maximum number of selectable items in "multiple" mode
     */
    max: Number,
    /**
     * Allow selecting multiple items
     */
    multiple: {
      type: Boolean,
      default: false,
    },
    /**
     * Available options in the picker
     */
    options: {
      type: [Array, Object, Function],
      default() {
        return [];
      }
    },
    /**
     * Enable search input
     */
    search: Boolean,
    /**
     * Controll the toggle for each item with the
     * return value of the given function
     */
    toggle: Function,
    /**
     * Array of selected items (array of ids)
     */
    value: {
      type: Array,
      default() {
        return [];
      }
    }
  },
  data() {
    return {
      selected: this.value,
      parents: [],
      q: null
    };
  },
  computed: {
    items() {
      return async () => {
        // Async options
        if (typeof this.options === 'function') {
          return await this.options({
            ...this.pagination,
            search: this.q,
            parent: this.parent
          });
        }

        // Array/Object of options
        return {
          data: this.$helper.clone(this.options),
          pagination: this.pagination
        };
      }
    },
    listeners() {
      return {
        ...this.$listeners,
        item: this.onItem,
        flag: this.onFlag,
        option: this.onOption,
        paginate: this.onPaginate,
        sort: this.onSort
      };
    },
    parent() {
      if (this.parents.length === 0) {
        return null;
      }

      return this.parents[this.parents.length - 1];
    }
  },
  watch: {
    options() {
      this.reload();
    },
    pagination() {
      this.reload();
    },
    q: debounce(function () {
      this.onPaginate({ ...this.pagination, page: 1 });
      this.reload();
    }, 250),
    value() {
      this.selected = this.value;
      this.map();
    },
  },
  methods: {
    map() {
      const max = this.multiple && this.max && this.selected.length >= this.max;

      this.data = this.data.map(item => {
        const selected = this.selected.includes(item.id);

        // custom toggle function
        if (this.toggle) {
          item.flag = this.toggle(item, selected, max);

        // selected
        } else if (selected) {
          item.flag = {
            icon: this.multiple ? "check" : "circle-filled",
            tooltip: "Deselect",
            color: "green"
          };

        // unselected
        } else {
          item.flag = {
            icon: "circle-outline",
            tooltip: "Select",
            color: max ? "gray" : null,
            disabled: Boolean(max)
          };
        }

        // Disable links in picker
        delete item.link;

        return item;
      });
    },
    onBack() {
      this.parents.pop();
      this.reset();
      this.reload();
    },
    onDeselect(id, item, itemIndex) {
      const index = this.selected.indexOf(id);

      if (index !== -1) {
        this.$delete(this.selected, index);
      }

      this.onInput();
    },
    onFlag(item, itemIndex) {
      if (this.selected.includes(item.id)) {
        this.onDeselect(item.id, item, itemIndex);
      } else {
        this.onSelect(item.id, item, itemIndex);
      }
    },
    onInput() {
      this.$emit("input", this.selected);
    },
    onItem(item, itemIndex) {
      this.onFlag(item, itemIndex);
    },
    onOption(option, item, itemIndex) {
      if (option === "enter") {
        this.parents.push(item);
        this.reset();
        this.reload();
      }

      this.$emit("option", option, item, itemIndex);
    },
    onPaginate(pagination) {
      this.$emit("paginate", pagination);
    },
    onSelect(id, item, itemIndex) {
      if (this.multiple === false) {
        this.selected = [];
      }

      if (this.multiple && this.max && this.selected.length >= this.max) {
        // don't allow to add more items
        return;
      }

      this.selected.push(id);
      this.onInput();
    },
    onSort(items, event) {
      this.selected = items.map(item => item.id);
      this.onInput();
    },
    reset() {
      this.q = null;
      this.onPaginate({ ...this.pagination, page: 1 });
    }
  }
};
</script>

<style lang="scss">
.k-picker-search {
  background: $color-gray-300;
}
</style>
