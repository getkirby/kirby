
export default {
  props: {
    counter: {
      type: Boolean,
      default: true
    }
  },
  computed: {
    counterOptions() {
      if (this.value === null || this.disabled || this.counter === false) {
        return false;
      }

      let count = 0;

      if (this.value) {
        if (Array.isArray(this.value)) {
          count = this.value.length;
        } else {
          count = String(this.value).length;
        }
      }
      return {
        count: count,
        min: this.min,
        max: this.max,
      };
    }
  },
}