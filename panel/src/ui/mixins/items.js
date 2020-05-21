
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
     * Available options: `tiny`|`small`|`default`|`medium`|`large`
     */
    size: {
      type: String,
      default: "default"
    },
  }
}
