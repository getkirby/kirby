<template>
  <k-field :input="_uid" v-bind="$props">

    <!-- Actions button/dropdown -->
    <template v-slot:options>
      <k-options-dropdown
        v-if="hasActions"
        v-bind="actionsOptions"
        @option="onAction"
      />
    </template>

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
      v-bind="collectionOptions"
      @empty="onEmpty"
      @option="onRemove"
      @sort="onSort"
    />

    <!-- Drawer & Picker -->
    <k-drawer
      v-if="hasOptions"
      v-bind="drawerOptions"
      ref="drawer"
      @close="$refs.picker.reset()"
      @submit="onSelect"
    >
      <k-picker
        ref="picker"
        v-model="drawer.value"
        v-bind="pickerOptions"
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
    this.$delete(this.$options.props, "loader");
    this.$delete(this.$options.props, "items");
  },
  props: {
    ...Field.props,
    forItems: {
      type: Function
    },
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
      // only show select action when options available
      if (this.hasOptions) {
        return [
          { icon: "circle-nested", text: this.$t("select"), click: "select" }
        ];
      }

      return [];
    },
    actionsOptions() {
      return {
        options: this.actions,
        text: this.actions.length < 2
      };
    },
    collectionOptions() {
      return {
        ...this.collection,
        help: false
      };
    },
    drawerOptions() {
      return {
        loading: this.drawer.loading,
        title: this.label + " / " + this.$t("select"),
        size: this.picker.width || "small"
      };
    },
    hasActions() {
      if (this.disabled) {
        return false;
      }

      return this.actions.length > 0;
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
    pickerOptions() {
      return {
        ...this.picker,
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
     */
    async getItems(ids) {
      return [];
    },
    /**
     * Returns item objects.
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
    onDrop(event) {},
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
      setTimeout(() => {
        this.$refs.picker.$refs.search.focus();
      }, 50);
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
