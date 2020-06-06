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

    <!-- Drawer with picker -->
    <component
      :is="'k-' + type + '-dialog'"
      ref="dialog"
      v-bind="dialogOptions"
      @submit="onSelect"
    />
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
    hasOptions: {
      type: Boolean,
      default: true
    },
    info: String,
    link: Boolean,
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
    type: {
      type: String,
      default: "models"
    },
    value: {
      type: Array,
      default() {
        return [];
      }
    }
  },
  data() {
    return {
      selected: this.value
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
        help: false,
        sortable: this.isSortable
      };
    },
    dialogOptions() {
      let options = {
        ...this.picker,
        hasDrop: this.hasDrop,
        limit: this.picker.limit || 15,
        max: this.max,
        multiple: this.multiple,
        search: this.search,
        width: this.picker.width || "small",
        title: this.label + " / " + this.$t("select"),
      };

      // provided a custom async function for options
      if (this.options) {
        options.options = this.options;

      // use API endpoint for options
      } else if (this.endpoints) {
        options.endpoint = this.endpoints.field + "/options";
      }

      return options;
    },
    hasActions() {
      if (this.disabled) {
        return false;
      }

      return this.actions.length > 0;
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
    loader() {
      let options = [];

      if (!this.disabled) {
        options.push({
          icon: "remove",
          text: "Remove",
          disabled: this.disabled
        });
      }

      return {
        info: this.info,
        limit: this.value.length || 1,
        options: options
      }
    },
    more() {
      if (!this.multiple && this.selected.length >= 1) {
        return false;
      }

      if (!this.max) {
        return true;
      }

      return this.max > this.selected.length;
    }
  },
  watch: {
    value() {
      if (JSON.stringify(this.selected) !== JSON.stringify(this.value)) {
        this.selected = this.value;
        this.reload();
      }
    }
  },
  methods: {
    async items() {
      if (this.selected.length === 0) {
        return [];
      }

      return this.$api.get(this.endpoints.field + "/items", {
        ids: this.selected.join(",")
      });
    },
    map() {
      this.data = this.data.map(item => {
        if (this.link === false) {
          delete item.link;
        }

        item.options = this.loader.options;
        return item;
      });
    },
    onAction(option, item, itemIndex) {
      switch (option) {
        case "select":
          return this.onOpen();
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
    onOpen() {
      this.$refs.dialog.open(this.selected);
    },
    onRemove(option, item, itemIndex) {
      this.selected.splice(itemIndex, 1);
      this.data.splice(itemIndex, 1);
      this.onInput();
    },
    onSelect(value) {
      this.selected = value;
      this.onInput(true);
    },
    onSort(items) {
      this.selected = items.map(item => item.id)
      this.onInput();
    }
  }
}
</script>
