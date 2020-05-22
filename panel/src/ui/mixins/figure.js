
export default {
  props: {
    /**
     * Available options: `list`|`cardlets`|`cards`
     */
    layout: {
      type: String,
      default: "list"
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
