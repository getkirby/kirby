export default {
  data() {
    return {
      models: [],
      issue: null,
      options: {
        endpoint: null,
        max: null,
        multiple: true,
        search: false,
        selected: []
      },
      search: null
    }
  },
  computed: {
    multiple() {
      return this.options.multiple === true && this.options.max !== 1;
    },
    checkedIcon() {
      return this.multiple === true ? "check" : "circle-filled";
    },
    filtered() {
      if (this.search === null) {
        return this.models;
      }

      return this.models.filter(model => this.isFiltered(model));
    },
    selected() {
      return this.models.filter(model => model.selected);
    }
  },
  methods: {
    fetch() {
      this.models = [];

      return this.$api
        .get(this.options.endpoint)
        .then(response => {
          const models   = response.data || response;
          const selected = this.options.selected || [];

          this.models = models.map(model => {
            return {
              ...model,
              selected: selected.indexOf(model[this.id || "id"]) !== -1
            };
          });
        })
        .catch(e => {
          this.models = [];
          this.issue = e.message;
        });
    },
    open(options) {
      this.options = {
        ...this.options,
        ...options
      };
      this.fetch().then(() => {
        this.$refs.dialog.open();
      });
    },
    submit() {
      this.$emit("submit", this.selected);
      this.$refs.dialog.close();
    },
    toggle(index) {
      // transform index of filtered models to index of all models
      index = this.models.findIndex(model => model[this.id || "id"] === this.filtered[index].id);

      if (this.options.multiple === false) {
        this.models = this.models.map(model => {
          return {
            ...model,
            selected: false
          };
        });
      }

      if (this.models[index].selected === true) {
        this.models[index].selected = false;
        return;
      }

      if (this.options.max && this.options.max <= this.selected.length) {
        return;
      }

      this.models[index].selected = true;
    }
  }
};
