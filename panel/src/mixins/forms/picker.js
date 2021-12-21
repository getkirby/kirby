import { props as Field } from "@/components/Forms/Field.vue";

export default {
  mixins: [Field],
  inheritAttrs: false,
  props: {
    empty: String,
    info: String,
    link: Boolean,
    /**
     * Switches the layout of the items
     * @values list, cards
     */
    layout: {
      type: String,
      default: "list"
    },
    max: Number,
    /**
     * If false, only a single item can be selected
     */
    multiple: Boolean,
    parent: String,
    search: Boolean,
    size: String,
    text: String,
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
    btnIcon() {
      if (!this.multiple && this.selected.length > 0) {
        return "refresh";
      }

      return "add";
    },
    btnLabel() {
      if (!this.multiple && this.selected.length > 0) {
        return this.$t("change");
      }

      return this.$t("add");
    },
    isInvalid() {
      if (this.required && this.selected.length === 0) {
        return true;
      }

      if (this.min && this.selected.length < this.min) {
        return true;
      }

      if (this.max && this.selected.length > this.max) {
        return true;
      }

      return false;
    },
    items() {
      return this.models.map(this.item);
    },
    more() {
      if (!this.max) {
        return true;
      }

      return this.max > this.selected.length;
    }
  },
  watch: {
    value(value) {
      this.selected = value;
    }
  },
  methods: {
    focus() {},
    item(item) {
      return item;
    },
    onInput() {
      this.$emit("input", this.selected);
    },
    open() {
      if (this.disabled) {
        return false;
      }

      this.$refs.selector.open({
        endpoint: this.endpoints.field,
        max: this.max,
        multiple: this.multiple,
        search: this.search,
        selected: this.selected.map((model) => model.id)
      });
    },
    remove(index) {
      this.selected.splice(index, 1);
      this.onInput();
    },
    removeById(id) {
      this.selected = this.selected.filter((item) => item.id !== id);
      this.onInput();
    },
    select(items) {
      if (items.length === 0) {
        this.selected = [];
        return;
      }

      // remove all items that are no longer selected
      this.selected = this.selected.filter((selected) => {
        return items.filter((item) => item.id === selected.id).length > 0;
      });

      // add items that are not yet in the selected list
      items.forEach((item) => {
        if (
          this.selected.filter((selected) => item.id === selected.id).length ===
          0
        ) {
          this.selected.push(item);
        }
      });

      this.onInput();
    }
  }
};
