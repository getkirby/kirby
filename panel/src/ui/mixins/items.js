
import figure from "./figure.js";

export default {
  mixins: [figure],
  props: {
    /**
     * Placeholder text and icon for empty state
     */
    empty: {
      type: [String, Object]
    },
    /**
     * Card sizes.
     */
    size: {
      type: String,
      default: "default",
      validator: (prop) => [
        "tiny",
        "small",
        "default",
        "medium",
        "large"
      ].includes(prop)
    },
  }
}
