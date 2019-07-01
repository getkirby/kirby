export default {
  data() {
    return {
      models: [],
      issue: null,
      selected: {},
      options: {
        endpoint: null,
        max: null,
        multiple: true,
        parent: null,
        selected: [],
      }
    }
  },
  computed: {
    multiple() {
      return this.options.multiple === true && this.options.max !== 1;
    },
    checkedIcon() {
      return this.multiple === true ? "check" : "circle-filled";
    }
  },
  methods: {
    fetch() {
      return this.$api
        .get(this.options.endpoint, this.fetchData || {})
        .then(response => {
          this.models = response.data || response.pages || response;

          if (this.onFetched) {
            this.onFetched(response);
          }
        })
        .catch(e => {
          this.models = [];
          this.issue  = e.message;
        });
    },
    open(files, options) {

      let fetch = true;

      if (Array.isArray(files)) {
        this.models = files;
        fetch       = false;
      } else {
        this.models = [];
        options     = files;
      }

      this.options = {
        ...this.options,
        ...options
      };

      this.selected = {};

      this.options.selected.forEach(id => {
        this.$set(this.selected, id, {
          id: id
        });
      });

      if (fetch) {
        this.fetch().then(() => {
          this.$refs.dialog.open();
        });
      } else {
        this.$refs.dialog.open();
      }
    },
    submit() {
      this.$emit("submit", Object.values(this.selected));
      this.$refs.dialog.close();
    },
    isSelected(item) {
      return this.selected[item.id] !== undefined;
    },
    toggle(item) {
      if (this.options.multiple === false) {
        this.selected = {};
      }

      if (this.isSelected(item) === true) {
        this.$delete(this.selected, item.id);
        return;
      }

      if (this.options.max && this.options.max <= Object.keys(this.selected).length) {
        return;
      }

      this.$set(this.selected, item.id, item);
    }
  }
};
