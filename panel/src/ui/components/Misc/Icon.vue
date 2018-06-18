<template>
  <i
    :aria-label="alt"
    :role="alt ? 'img' : null"
    :aria-hidden="!alt"
    :data-back="back"
    class="kirby-icon"
  >
    <span v-if="emoji" class="kirby-icon-emoji">{{ type }}</span>
    <svg v-else viewBox="0 0 16 16">
      <use :xlink:href="'#icon-' + type" />
    </svg>
  </i>
</template>

<script>
export default {
  props: {
    alt: String,
    back: String,
    emoji: Boolean,
    type: String,
  }
};
</script>

<style lang="scss">
.kirby-icon {
  position: relative;
  line-height: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.kirby-icon svg {
  width: 1rem;
  height: 1rem;
}
.kirby-icon svg * {
  fill: currentColor;
}
.kirby-icon[data-back="black"] {
  background: $color-dark;
  color: $color-white;
}
.kirby-icon[data-back="white"] {
  background: $color-white;
}
.kirby-icon[data-back="pattern"] {
  background: lighten($color-dark, 10%) url($pattern);
  color: $color-white;
}
.kirby-icon-emoji {
  display: block;
  line-height: 1;
  font-style: normal;
  font-size: 1rem;
}
</style>
