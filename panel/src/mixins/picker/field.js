import Field from "@/components/Forms/Field.vue";

export default {
  inheritAttrs: false,
  props: {
    ...Field.props,
    empty: String,
    info: String,
    link: Boolean,
    layout: String,
    max: Number,
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
    elements() {
      const layouts = {
        cards: {
          list: "k-cards",
          item: "k-card"
        },
        list: {
          list: "k-list",
          item: "k-list-item"
        }
      };

      if (layouts[this.layout]) {
        return layouts[this.layout];
      }

      return layouts["list"];
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
    onInput() {
      this.$emit("input", this.selected);
    },
    remove(index) {
      this.selected.splice(index, 1);
      this.onInput();
    },
    removeById(id) {
      this.selected = this.selected.filter(item => item.id !== id);
      this.onInput();
    },
    select(items) {
      if (items.length === 0) {
        this.selected = [];
        return;
      }

      // remove all items that are no longer selected
      this.selected = this.selected.filter(selected => {
        return items.filter(item => item.id === selected.id).length > 0;
      });

      // add items that are not yet in the selected list
      items.forEach(item => {
        if (this.selected.filter(selected => item.id === selected.id).length === 0) {
          this.selected.push(item);
        }
      });

      this.onInput();
    }
  }
};
