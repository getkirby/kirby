
export default {
  props: {
    layout: {
      type: String,
      default: "list",
      validator: (prop) => [
        "list",
        "cardlets",
        "cards",
      ].includes(prop)
    },
    /**
     * Global preview image/icon settings for items
     */
    preview: {
      type: [Object, Boolean],
      default: true
    }
  }
}
