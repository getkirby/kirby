export default {
  data() {
    return {
      models: [],
      issue: null,
      options: {
        endpoint: null,
        max: null,
        multiple: true,
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
    selected() {
      return this.models.filter(model => model.selected);
    }
  },
  methods: {
    fetch() {
      this.models = [];

      return this.$api
        .get(this.options.endpoint, this.fetchData || {})
        .then(response => {
          const models   = response.data || response.pages || response;
          const selected = this.options.selected || [];

          this.models = models.map(model => {
            return {
              ...model,
              selected: selected.indexOf(model[this.id || "id"]) !== -1
            };
          });

          if (this.onFetched) {
            this.onFetched(response);
          }
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
