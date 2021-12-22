<template>
  <k-button-group :buttons="buttons" class="k-prev-next" />
</template>

<script>
/**
 * Group of buttons with left- and right-pointing arrows
 * used for navigation between previous and next items
 * @example <k-prev-next :prev="{ link: 'a' }" :next="false" />
 * @public
 */
export default {
  props: {
    /**
     * Button configuraiton for left-pointing arrow
     */
    prev: {
      type: [Boolean, Object],
      default: false
    },
    /**
     * Button configuraiton for right-pointing arrow
     */
    next: {
      type: [Boolean, Object],
      default: false
    }
  },
  computed: {
    /**
     * Array of button configurations to be passed
     * to ButtonGroup component
     * @returns {array}
     */
    buttons() {
      return [
        { ...this.button(this.prev), icon: "angle-left" },
        { ...this.button(this.next), icon: "angle-right" }
      ];
    }
  },
  methods: {
    /**
     * Returns button configuration with
     * defaults for disabled button
     * @param {bool|Object} config
     * @returns {Object}
     */
    button(config) {
      if (!config) {
        return {
          disabled: true,
          link: "#"
        };
      }

      return config;
    }
  }
};
</script>

<style>
.k-prev-next {
  direction: ltr;
}
</style>
