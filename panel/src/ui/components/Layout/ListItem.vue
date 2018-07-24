<template>
  <component :is="element" class="kirby-list-item" v-on="$listeners">
    <kirby-icon v-if="sortable" class="kirby-sort-handle" type="sort" />
    <kirby-link
      v-tab
      :to="link"
      :target="target"
      class="kirby-list-item-content"
    >
      <figure class="kirby-list-item-image">
        <kirby-image
          v-if="image && image.url"
          :src="image.url"
          :back="image.back || 'pattern'"
          :cover="image.cover"
        />
        <kirby-icon v-else v-bind="icon" />
      </figure>
      <figcaption class="kirby-list-item-text">
        <em>{{ text }}</em>
        <small v-if="info">{{ info }}</small>
      </figcaption>
    </kirby-link>
    <div class="kirby-list-item-options">
      <slot name="options">
        <kirby-button
          v-if="flag"
          v-bind="flag"
          @click="flag.click"
        />
        <kirby-button
          v-if="options"
          icon="dots"
          alt="Options"
          class="kirby-list-item-toggle"
          @click.stop="$refs.options.toggle()"
        />
        <kirby-dropdown-content
          ref="options"
          :options="options"
          align="right"
          @action="$emit('action', $event)"
        />
      </slot>
    </div>
  </component>
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    element: {
      type: String,
      default: "li"
    },
    image: Object,
    icon: {
      type: Object,
      default() {
        return {
          type: "file",
          back: "black"
        };
      }
    },
    sortable: Boolean,
    text: String,
    target: String,
    info: String,
    link: String,
    flag: Object,
    options: [Array, Function]
  }
};
</script>

<style lang="scss">

$list-item-height: 38px;


.kirby-list-item {
  position: relative;
  display: flex;
  align-items: center;
  background: $color-white;
  border-radius: $border-radius;
  margin-bottom: 2px;
  box-shadow: $box-shadow-card;
}
.kirby-list-item .kirby-sort-handle {
  position: absolute;
  left: -$list-item-height;
  width: $list-item-height;
  height: $list-item-height;
  color: $color-dark;
  opacity: 0;
  z-index: 1;
  cursor: -webkit-grab;
  will-change: opacity, color;
  transition: opacity .3s;
}
.kirby-list-item .kirby-sort-handle:active {
  cursor: -webkit-grabbing;
}
.kirby-list:hover .kirby-sort-handle {
  opacity: .25;
}
.kirby-list-item:hover .kirby-sort-handle {
  opacity: 1;
}

.kirby-list-item-image {
  width: $list-item-height;
  height: $list-item-height;
  overflow: hidden;
  flex-shrink: 0;
  line-height: 0;
}
.kirby-list-item-image .kirby-image {
  width: $list-item-height;
  height: $list-item-height;
  object-fit: contain;
}
.kirby-list-item-image .kirby-icon {
  width: $list-item-height;
  height: $list-item-height;
}
.kirby-list-item-image .kirby-icon svg {
  color: rgba($color-white, .5);
}
.kirby-list-item-content {
  display: flex;
  align-items: center;
  flex-grow: 1;
  flex-shrink: 1;
  overflow: hidden;
  @include highlight-tabbed;
}
.kirby-list-item-text {
  display: flex;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  align-items: baseline;
  width: 100%;
  line-height: 1;
  padding: .5rem .75rem;
}
.kirby-list-item-text em {
  font-style: normal;
  margin-right: 1rem;
  flex-grow: 1;
  min-width: 0;
  overflow: hidden;
  text-overflow: ellipsis;
  font-size: $font-size-small;
  color: $list-item-text-color;
}
.kirby-list-item-text small {
  color: $color-light-grey;
  font-size: $font-size-tiny;
  color: $list-item-info-color;
  display: none;
  min-width: 0;
  overflow: hidden;
  text-overflow: ellipsis;

  @media screen and (min-width: $breakpoint-small) {
    display: block;
  }
}
.kirby-list-item-options {
  position: relative;
  flex-shrink: 0;
}
.kirby-list-item-options .kirby-dropdown-content {
  top: $list-item-height;
}
.kirby-list-item-options > .kirby-button {
  height: $list-item-height;
  padding: 0 12px;
}
.kirby-list-item-options > .kirby-button .kirby-icon {
  height: $list-item-height;
}
</style>
