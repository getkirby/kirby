<template>
  <k-field
    :input="_uid"
    v-bind="$props"
    class="k-picker-field"
  >
    <!-- Actions button/dropdown -->
    <k-options-dropdown
      v-if="!disabled && actions.length"
      :options="actions"
      :text="actionsLabel"
      @option="onAction"
      slot="options"
    />

    <!-- Collection -->
    <k-async-collection
      ref="collection"
      v-bind="collection"
      :data-has-actions="this.actions.length > 0"
      @empty="onEmpty"
      @option="onRemove"
      @sort="onSort"
    />

    <!-- Drawer & Picker -->
    <k-drawer
      ref="drawer"
      :title="label + ' / ' + $t('select')"
      :submit-button="true"
      :size="picker.size || 'small'"
      @close="$refs.picker.reset()"
      @submit="onSelect"
    >
      <k-picker
        ref="picker"
        v-model="temp"
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

export default {
  props: {
    ...Field.props,
    empty: [String, Object],
    hasOptions: {
      type: Boolean,
      default: true
    },
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
      temp: null,
      pagination: {
        page: 1,
        limit: this.picker.limit || 15,
        total: 0
      }
    };
  },
  computed: {
    actions() {
      // only show select action if items are available
      // as options in the picker
      if (this.hasOptions) {
        return [
          { icon: "circle-nested", text: this.$t("select"), click: "select" }
        ];
      }

      return [];
    },
    actionsLabel() {
      if (this.actions.length > 1) {
        return false;
      }

      return this.actions[0].text;
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
    }
  },
  watch: {
    value() {
      this.selected = this.value;
      this.$refs.collection.reload();
    }
  },
  methods: {
    /**
     * Takes a list of ids and return full item objects.
     * Has to be implemented by actual field
     */
    async getItems(ids) {
      return [];
    },
    /**
     * Returns item objects.
     * Has to be implemented by actual field
     */
    async getOptions({page, limit, parent, search}) {
      return [];
    },
    onAction(option, item, itemIndex) {
      switch (option) {
        case "select":
          this.onOpen();
          break;
      }
    },
    onEmpty() {
      if (this.actions.length > 0) {
        this.onAction(this.actions[0].click);
      }
    },
    onInput() {
      this.$emit("input", this.selected);
    },
    onOpen() {
      this.temp = this.$helper.clone(this.selected);
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
      this.selected = this.temp;
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

<style>
.k-picker-field > .k-collection:not([data-has-actions]) .k-empty {
  cursor: default;
}
</style>
