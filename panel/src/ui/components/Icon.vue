<template>
  <span
    :aria-hidden="!alt"
    :aria-label="alt"
    :class="classNames"
    :data-back="back"
    :data-size="size"
    :role="alt ? 'img' : null"
  >
    <span
      v-if="emoji"
      class="k-icon-emoji"
    >{{ type }}</span>
    <svg
      v-else
      viewBox="0 0 16 16"
    >
      <use :xlink:href="'#icon-' + type" />
    </svg>
  </span>
</template>

<script>
export default {
  props: {
    /**
     * For better accessibility of icons, you can pass an additional 
     * alt attribute like for images.
    emoji: Boolean,
     */
    alt: String,
    color: String,
    /**
     * Like with the `k-image` component, you can set the background for 
     * the icon. By default, the background is transparent.  
     * Available options: `black`|`white`|`pattern`
     */
    back: String,
    /**
     * By default the icon size is set to `1rem = 16px`, 
     * which corresponds with the Panel font size. 
     * Additional sizes are: `regular`|`medium`|`large`
     */
    size: String,
    /**
     * Select the icon with this attribute.
     */
    type: String
  },
  computed: {
    classNames() {
      return [
        "k-icon",
        "k-icon-" + this.type,
        this.back      ? "bg-"   + this.back      : false,
        this.iconColor ? "text-" + this.iconColor : false
      ];
    },
    iconColor() {
      if (this.color) {
        return this.color;
      }

      switch (this.back) {
        case "black":
        case "pattern":
          return "white";
      }

      return null;
    }
  }
};
</script>

<style lang="scss">
.k-icon {
  line-height: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.k-icon svg {
  width: 1rem;
  height: 1rem;
  -moz-transform: scale(1);
}
.k-icon svg * {
  fill: currentColor;
}
.k-icon[data-size="medium"] svg {
  width: 2rem;
  height: 2rem;
}
.k-icon[data-size="large"] svg {
  width: 3rem;
  height: 3rem;
}
.k-icon-emoji {
  display: block;
  line-height: 1;
  font-style: normal;
  font-size: 1rem;
}

/* fix emoji alignment on high-res screens */
@media only screen and (-webkit-min-device-pixel-ratio: 2), not all, not all, not all, only screen and (min-resolution: 192dpi), only screen and (min-resolution: 2dppx) {
  .k-icon-emoji {
    font-size: 1.25rem;
    margin-left: .2rem;
  }
}

</style>
