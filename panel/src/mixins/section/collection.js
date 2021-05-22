export default {
  inheritAttrs: false,
  props: {
    column: Number,
    data: Array,
    empty: String,
    headline: String,
    layout: String,
    help: String,
    link: String,
    max: Number,
    min: Number,
    name: String,
    pagination: Object,
    parent: String,
    size: String,
    sortable: Boolean,
    width: String
  },
  data() {
    return {
      isProcessing: false
    };
  },
  computed: {
    isInvalid() {
      if (this.min && this.data.length < this.min) {
        return true;
      }

      if (this.max && this.data.length > this.max) {
        return true;
      }

      return false;
    },
    items() {
      return this.data;
    }
  },
  methods: {
    paginate(pagination) {
      this.$reload({
        data: { [`${this.name}[page]`]: pagination.page },
        only: `$props.tab.columns.${this.column}.sections.${this.name}`
      });
    },
  }
}