<template>
  <k-field
    :input="_uid"
    v-bind="$props"
    class="k-picker-field"
  >
    <!-- Add button -->
    <template slot="options">
      <k-button
        v-if="!disabled"
        :id="_uid"
        ref="button"
        :icon="btnIcon"
        @click="onOpen"
      >
        {{ btnLabel }}
      </k-button>
    </template>

    <!-- Collection -->
    <k-async-collection
      ref="collection"
      v-bind="collection"
      @empty="onEmpty"
      @option="onRemove"
      @sort="onSort"
    />

    <!-- Drawer & Picker -->
    <k-drawer
      ref="drawer"
      :title="label + ' / ' + btnLabel"
      :submit-button="true"
      :size="picker.size || 'small'"
      @close="$refs.picker.reset()"
      @submit="onSelect"
    >
      <component
        :is="component"
        ref="picker"
        v-model="updated"
        v-bind="picker"
        :max="max"
        :multiple="multiple"
        :options="getOptions"
        :search="search"
        :pagination="pagination"
        @paginate="onPaginate"
      />
    </k-drawer>
  </k-field>
</template>

<script>
import Field from "@/ui/components/Field.vue";

// Dummy data
import { getItems, getOptions } from "../storybook/PickerItems.js";

export default {
  props: {
    ...Field.props,
    empty: [String, Object],
    image: {
      type: [Object, Boolean],
      default: true,
    },
    info: String,
    /**
     * Available options: `list`|`cardlets`|`cards`
     */
    layout: {
      type: String,
      default: "list"
    },
    limit: Number,
    /**
     * Maximum number of items
     */
    max: Number,
    multiple: {
      type: Boolean,
      default: true
    },
    picker: {
      type: [Object],
      default() {
        return {};
      }
    },
    /**
     * Show search input above picker
     */
    search: {
      type: Boolean,
      default: true
    },
    /**
     * Allow manual sorting via drag-and-drop
     */
    sortable: {
      type: Boolean,
      default: true
    },
    sortBy: String,
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
      updated: null,
      pagination: {
        page: 1,
        limit: 15,
        total: 0,
        ...this.picker.pagination
      }
    };
  },
  computed: {
    btnMode() {
      if (!this.more) {
        return "change";
      }

       if (!this.multiple && this.selected.length > 0) {
        return "change";
      }

      return "add"
    },
    btnIcon() {
      if (this.btnMode === "change") {
        return "refresh";
      }

      return this.btnMode;
    },
    btnLabel() {
      return this.$t(this.btnMode);
    },
    collection() {
      let options = [];

      if (this.multiple) {
        options.push({
          icon: "remove",
          text: "Remove",
          disabled: this.disabled
        });
      }

      return {
        empty: this.empty,
        image: this.image,
        items: async () => {
          const items = await this.getItems(this.selected);
          return items.map(item => {
            item.options = options;
            return item;
          });
        },
        layout: this.layout,
        limit: this.limit,
        loader: {
          info: this.info,
          limit: this.value.length,
          options: options
        },
        pagination: false,
        sortable: this.isSortable
      };
    },
    component() {
      return "k-picker";
    },
    isSortable() {
      if (this.disabled === true) {
        return false;
      }

      if (this.selected.length <= 1) {
        return false;
      }

      if (this.sortable === false) {
        return false;
      }

      if (this.sortBy) {
        return false;
      }

      return true;
    },
    more() {
      if (!this.max) {
        return true;
      }

      return this.max > this.selected.length;
    }
  },
  watch: {
    value() {
      this.selected = this.value;
      this.$refs.collection.reload();
    }
  },
  methods: {
    async getItems(ids) {
      return getItems(ids, "Item", false);
    },
    async getOptions({page, limit, parent, search}) {
      return getOptions(page, limit, parent, false, search, "Item");
    },
    onEmpty() {
      this.onOpen();
    },
    onInput() {
      this.$emit("input", this.selected);
    },
    onOpen() {
      this.updated = this.$helper.clone(this.selected);
      this.$refs.drawer.open();
    },
    onPaginate(pagination) {
      this.pagination = pagination;
    },
    onRemove(option, item, itemIndex) {
      this.selected.splice(itemIndex, 1);
      this.onInput();
    },
    onSelect() {
      this.selected = this.updated;
      this.onInput();
      this.$refs.drawer.close();
    },
    onSort(items) {
      this.selected = items.map(item => item.id)
      this.onInput();
    }
  }
}
</script>
