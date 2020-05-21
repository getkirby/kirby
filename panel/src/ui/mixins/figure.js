
export default {
  props: {
    /**
     * Global icon settings for items
     */
    icon: {
      type: [Object, Boolean],
      default: true,
    },
    /**
     * Global image settings for items
     */
    image: {
      type: [Object, Boolean],
      default: true,
    },
    /**
     * Available options: `list`|`cardlets`|`cards`
     */
    layout: {
      type: String,
      default: "list"
    }
  }
}
