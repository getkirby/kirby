export default {
  inheritAttrs: false,
  props: {
    accesskey: String,
    autofocus: Boolean,
    current: [String, Boolean],
    icon: [String, Object],
    id: [String, Number],
    responsive: Boolean,
    role: String,
    tabindex: String,
    text: String,
    theme: String,
    tooltip: String
  },
  computed: {
    iconOptions() {
      let defaults = {
        alt: this.tooltip || this.text
      };

      if (typeof this.icon === "object") {
        return {
          ...this.icon,
          ...defaults
        };
      }

      return {
        type: this.icon,
        ...defaults
      };
    }
  }
};
