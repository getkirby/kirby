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

    <!-- Error -->
    <k-error-items
      v-if="error"
      :layout="layout"
      :limit="value.length"
    >
      {{ error }}
    </k-error-items>

    <!-- Collection -->
    <k-collection
      v-else
      v-bind="collection"
      :data-has-actions="this.actions.length > 0"
      @empty="onEmpty"
      @option="onRemove"
      @sort="onSort"
    />

    <!-- Drawer & Picker -->
    <k-drawer
      ref="drawer"
      :loading="drawer.loading"
      :title="label + ' / ' + $t('select')"
      :size="picker.size || 'small'"
      @close="$refs.picker.reset()"
      @submit="onSelect"
    >
      <k-picker
        ref="picker"
        v-model="drawer.value"
        v-bind="selector"
        @paginate="onPaginate"
        @startLoading="onLoading"
        @stopLoading="onLoaded"
      />
    </k-drawer>
  </k-field>
</template>

<script>
import AsyncCollection from "@/ui/components/AsyncCollection.vue";
import Field from "@/ui/components/Field.vue";

export default {
  extends: AsyncCollection,
  beforeCreate: function(){
    this.$delete(this.$options.props, "items");
    this.$delete(this.$options.props, "loader");
  },
  props: {
    ...Field.props,
    hasOptions: {
      type: Boolean,
      default: true
    },
    info: String,
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
      drawer: {
        value: null,
        page: 1,
        loading: false
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
    items() {
      return async () => {
        return await this.getItems(this.selected);
      };
    },
    loader() {
      let options = [];

      if (this.multiple) {
        options.push({
          icon: "remove",
          text: "Remove",
          disabled: this.disabled
        });
      }

      return {
        info: this.info,
        limit: this.value.length,
        options: options
      }
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
    selector() {
      return {
        ...this.picker.items,
        max: this.max,
        multiple: this.multiple,
        options: this.getOptions,
        search: this.search,
        pagination: {
          page: this.drawer.page,
          limit: this.picker.limit || 15,
          total: 0
        }
      };
    }
  },
  watch: {
    value() {
      this.selected = this.value;
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
    map() {
      this.data = this.data.map(item => {
        item.options = this.loader.options;
        return item;
      });
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
    onInput(reload = false) {
      if (reload) {
        this.reload();
      }

      this.$emit("input", this.selected);
    },
    onLoading() {
      this.drawer.loading = true;
    },
    onLoaded() {
      this.drawer.loading = false;
    },
    onOpen() {
      this.drawer.value = this.$helper.clone(this.selected);
      this.$refs.drawer.open();
    },
    onPaginate(pagination) {
      this.drawer.page = pagination.page;
    },
    onRemove(option, item, itemIndex) {
      this.selected.splice(itemIndex, 1);
      this.data.splice(itemIndex, 1);
      this.onInput();
    },
    onSelect() {
      this.selected = this.drawer.value;
      this.onInput(true);
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
