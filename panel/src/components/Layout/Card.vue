<template>
  <figure class="k-card" v-on="$listeners">
    <k-sort-handle v-if="sortable" />

    <component :is="wrapper" :to="link" :target="target">
      <k-image
        v-if="imageOptions"
        v-bind="imageOptions"
        class="k-card-image"
      />
      <span v-else :style="'padding-bottom:' + ratioPadding" class="k-card-icon">
        <k-icon v-bind="icon" />
      </span>
      <figcaption class="k-card-content">
        <span :data-noinfo="!info" class="k-card-text">{{ text }}</span>
        <span v-if="info" class="k-card-info" v-html="info" />
      </figcaption>
    </component>

    <nav class="k-card-options">
      <k-button
        v-if="flag"
        v-bind="flag"
        class="k-card-options-button"
        @click="flag.click"
      />
      <slot name="options">
        <k-button
          v-if="options"
          :tooltip="$t('options')"
          icon="dots"
          class="k-card-options-button"
          @click.stop="$refs.dropdown.toggle()"
        />
        <k-dropdown-content
          ref="dropdown"
          :options="options"
          class="k-card-options-dropdown"
          align="right"
          @action="$emit('action', $event)"
        />
      </slot>
    </nav>

  </figure>
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    column: String,
    flag: Object,
    icon: {
      type: Object,
      default() {
        return {
          type: "file",
          back: "black"
        };
      }
    },
    image: Object,
    info: String,
    link: String,
    options: [Array, Function],
    sortable: Boolean,
    target: String,
    text: String
  },
  computed: {
    wrapper() {
      return this.link ? "k-link" : "div";
    },
    ratioPadding() {
      return this.icon && this.icon.ratio
        ? this.$helper.ratio(this.icon.ratio)
        : this.$helper.ratio("3/2");
    },
    imageOptions() {
      if (!this.image) {
        return false;
      }

      let src    = null;
      let srcset = null;

      if (this.image.cards) {
        src    = this.image.cards.url;
        srcset = this.image.cards.srcset;
      } else {
        src    = this.image.url;
        srcset = this.image.srcset;
      }

      if (!src) {
        return false;
      }

      return {
        src: src,
        srcset: srcset,
        back: this.image.back || "black",
        cover: this.image.cover,
        ratio: this.image.ratio || "3/2",
        sizes: this.getSizes(this.column)
      };
    }
  },
  methods: {
    getSizes(width) {
      switch (width) {
        case '1/2':
        case '2/4':
          return '(min-width: 30em) and (max-width: 65em) 59em, (min-width: 65em) 44em, 27em';
        case '1/3':
          return '(min-width: 30em) and (max-width: 65em) 59em, (min-width: 65em) 29.333em, 27em';
        case '1/4':
          return '(min-width: 30em) and (max-width: 65em) 59em, (min-width: 65em) 22em, 27em';
        case '2/3':
          return '(min-width: 30em) and (max-width: 65em) 59em, (min-width: 65em) 27em, 27em';
        case '3/4':
          return '(min-width: 30em) and (max-width: 65em) 59em, (min-width: 65em) 66em, 27em';
        default:
          return '(min-width: 30em) and (max-width: 65em) 59em, (min-width: 65em) 88em, 27em';
      }
    }
  }
};
</script>

<style lang="scss">
.k-card {
  position: relative;
  min-width: 0;
  background: $color-white;
  border-radius: $border-radius;
  box-shadow: $box-shadow-card;
}
.k-card a {
  min-width: 0;
  background: $color-white;
}
.k-card:focus-within {
  box-shadow: $color-focus 0 0 0 2px;
}
.k-card a:focus {
  outline: 0;
}

.k-card .k-sort-handle {
  position: absolute;
  top: 0.75rem;
  width: 2rem;
  height: 2rem;
  border-radius: $border-radius;
  background: $color-white;
  opacity: 0;
  color: $color-dark;
  z-index: 1;
  will-change: opacity;
  transition: opacity 0.3s;

  [dir="ltr"] & {
    right: 0.75rem;
  }
  [dir="rtl"] & {
    left: 0.75rem;
  }
}
.k-cards:hover .k-sort-handle {
  opacity: 0.25;
}
.k-card:hover .k-sort-handle {
  opacity: 1;
}

.k-card.k-sortable-ghost {
  outline: 2px solid $color-focus;
  border-radius: 0;
}

.k-card-image,
.k-card-icon {
  border-top-left-radius: $border-radius;
  border-top-right-radius: $border-radius;
  overflow: hidden;
}
.k-card-icon {
  position: relative;
  display: block;
}
.k-card-icon .k-icon {
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
}
.k-card-icon .k-icon-emoji {
  font-size: 3rem;
}
.k-card-icon .k-icon svg {
  width: 3rem;
  height: 3rem;
}

.k-card-content {
  line-height: 1.25rem;
  border-bottom-left-radius: $border-radius;
  border-bottom-right-radius: $border-radius;
  min-height: 2.25rem;
  padding: 0.5rem 0.75rem;
  overflow-wrap: break-word;
  word-wrap: break-word;
}

.k-card-text {
  display: block;
  font-weight: $font-weight-normal;
  text-overflow: ellipsis;
  font-size: $font-size-small;
}
.k-card-text[data-noinfo]:after {
  content: " ";
  height: 1em;
  width: 5rem;
  display: inline-block;
}
.k-card-info {
  color: $color-dark-grey;
  display: block;
  font-size: $font-size-small;
  text-overflow: ellipsis;
  overflow: hidden;

  [dir="ltr"] & {
    margin-right: 4rem;
  }

  [dir="rtl"] & {
    margin-left: 4rem;
  }
}

.k-card-options {
  position: absolute;
  bottom: 0;

  [dir="ltr"] & {
    right: 0;
  }

  [dir="rtl"] & {
    left: 0;
  }
}
.k-card-options > .k-button {
  position: relative;
  float: left;
  height: 2.25rem;
  padding: 0 0.75rem;
  line-height: 1;
}
.k-card-options-dropdown {
  top: 2.25rem;
}
</style>
