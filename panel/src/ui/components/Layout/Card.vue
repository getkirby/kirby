<template>
  <figure class="kirby-card" v-on="$listeners">
    <kirby-icon v-if="sortable" class="kirby-sort-handle" type="sort" />

    <component :is="wrapper" :to="link" :target="target">
      <kirby-image
        v-if="image && image.url"
        :src="image.url"
        :ratio="image.ratio || '3/2'"
        :back="image.back || 'black'"
        :cover="image.cover"
        class="kirby-card-image"
      />
      <span v-else :style="'padding-bottom:' + ratioPadding" class="kirby-card-icon">
        <kirby-icon v-bind="icon" />
      </span>
    </component>
    <figcaption>
      <div class="kirby-card-content">
        <component
          :is="wrapper"
          :to="link"
          :target="target"
          tabindex="-1"
        >
          <p class="kirby-card-text">{{ text }}</p>
          <p v-if="info" class="kirby-card-info">{{ info }}</p>
        </component>
      </div>
      <nav class="kirby-card-options">
        <kirby-button
          v-if="flag"
          v-bind="flag"
          class="kirby-card-options-button"
          @click="flag.click"
        />
        <template v-if="options">
          <kirby-button
            icon="dots"
            class="kirby-card-options-button"
            @click.stop="$refs.dropdown.toggle()"
          />
          <kirby-dropdown-content
            ref="dropdown"
            :options="options"
            class="kirby-card-options-dropdown"
            align="right"
            @action="$emit('action', $event)"
          />
        </template>
      </nav>
    </figcaption>
  </figure>
</template>

<script>
import ratioPadding from "../../helpers/ratioPadding.js";

export default {
  inheritAttrs: false,
  props: {
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
      return this.link ? "kirby-link" : "div";
    },
    ratioPadding() {
      return ratioPadding(this.image.ratio);
    }
  }
};
</script>

<style lang="scss">
.kirby-card {
  position: relative;
  min-width: 0;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  background: $color-white;
  border-radius: $border-radius;
  box-shadow: $box-shadow-card;
}
.kirby-sections > .kirby-column[data-width="1/4"] .kirby-card {
  grid-template-columns: repeat(auto-fit, minmax(175px, 1fr));
}
.kirby-card a {
  display: block;
  min-width: 0;
  height: 100%;
  background: $color-white;
}
.kirby-card:focus-within {
  box-shadow: $color-focus 0 0 0 2px;
}
.kirby-card a:focus {
  outline: 0;
}

.kirby-card .kirby-sort-handle {
  position: absolute;
  top: .175rem;
  left: 0;
  width: $list-item-height;
  height: $list-item-height;
  border-radius: 50%;
  opacity: 0;
  color: $color-white;
  z-index: 1;
  cursor: -webkit-grab;
  will-change: opacity;
  transition: opacity .3s;
}
.kirby-card .kirby-sort-handle:active {
  cursor: -webkit-grabbing;
}
.kirby-cards:hover .kirby-sort-handle {
  opacity: .25;
}
.kirby-card:hover .kirby-sort-handle {
  opacity: 1;
}
.kirby-card .kirby-sort-handle svg {
  filter: drop-shadow(0 1px 1px #000);
}



.kirby-card-content {
  padding: .625rem .75rem;
  line-height: 1.25rem;
  border-bottom-left-radius: $border-radius;
  border-bottom-right-radius: $border-radius;
  min-height: 2.25rem;
}
.kirby-card-image,
.kirby-card-icon {
  border-top-left-radius: $border-radius;
  border-top-right-radius: $border-radius;
  overflow: hidden;
}
.kirby-card-icon {
  position: relative;
  display: block;
}
.kirby-card-icon .kirby-icon {
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
}
.kirby-card-icon .kirby-icon-emoji {
  font-size: 3rem;
}
.kirby-card-icon .kirby-icon svg {
  width: 3rem;
  height: 3rem;
  color: rgba($color-white, 0.5);
}
.kirby-card-text {
  display: block;
  font-weight: $font-weight-normal;
  white-space: nowrap;
  line-height: 1.25;
  text-overflow: ellipsis;
  font-size: $font-size-small;
  overflow: hidden;
}
.kirby-card-info {
  color: $color-light-grey;
  white-space: nowrap;
  text-overflow: ellipsis;
  font-size: $font-size-small;
  padding-top: .25rem;
  overflow: hidden;
}
.kirby-card-options {
  position: absolute;
  bottom: 0;
  right: 0;
}
.kirby-card-options-button {
  position: relative;
  float: left;
  height: 2.25rem;
  padding: 0 .75rem;
  line-height: 1;
}
.kirby-card-options-dropdown {
  top: 2.25rem;
}
</style>
