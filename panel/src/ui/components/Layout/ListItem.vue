<template>
  <component :is="element" class="k-list-item" v-on="$listeners">
    <k-icon v-if="sortable" class="k-sort-handle" type="sort" />
    <k-link
      v-tab
      :to="link"
      :target="target"
      class="k-list-item-content"
    >
      <figure class="k-list-item-image">
        <k-image
          v-if="image && image.url"
          :src="image.url"
          :back="image.back || 'pattern'"
          :cover="image.cover"
        />
        <k-icon v-else v-bind="icon" />
      </figure>
      <figcaption class="k-list-item-text">
        <em>{{ text }}</em>
        <small v-if="info">{{ info }}</small>
      </figcaption>
    </k-link>
    <div class="k-list-item-options">
      <slot name="options">
        <k-button
          v-if="flag"
          v-bind="flag"
          @click="flag.click"
        />
        <k-button
          v-if="options"
          :tooltip="$t('options')"
          icon="dots"
          alt="Options"
          class="k-list-item-toggle"
          @click.stop="$refs.options.toggle()"
        />
        <k-dropdown-content
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


.k-list-item {
  position: relative;
  display: flex;
  align-items: center;
  background: $color-white;
  border-radius: $border-radius;
  margin-bottom: 2px;
  box-shadow: $box-shadow-card;
}
.k-list-item .k-sort-handle {
  position: absolute;
  left: -1.5rem;
  width: 1.5rem;
  height: $list-item-height;
  color: $color-dark;
  opacity: 0;
  z-index: 1;
  cursor: -webkit-grab;
  will-change: opacity, color;
  transition: opacity .3s;

  @media screen and (min-width: $breakpoint-small) {
    left: -$list-item-height;
    width: $list-item-height;
  }

}
.k-list-item .k-sort-handle:active {
  cursor: -webkit-grabbing;
}
.k-list:hover .k-sort-handle {
  opacity: .25;
}
.k-list-item:hover .k-sort-handle {
  opacity: 1;
}
.k-list-item.sortable-ghost {
  position: relative;
  outline: 2px solid $color-focus;
  z-index: 1;
  box-shadow: rgba($color-dark, 0.25) 0 5px 10px;
}
.k-list-item.sortable-fallback {
  opacity: .25 !important;
  overflow: hidden;
}
.k-list-item-image {
  width: $list-item-height;
  height: $list-item-height;
  overflow: hidden;
  flex-shrink: 0;
  line-height: 0;
}
.k-list-item-image .k-image {
  width: $list-item-height;
  height: $list-item-height;
  object-fit: contain;
}
.k-list-item-image .k-icon {
  width: $list-item-height;
  height: $list-item-height;
}
.k-list-item-image .k-icon svg {
  color: rgba($color-white, .5);
}
.k-list-item-content {
  display: flex;
  align-items: center;
  flex-grow: 1;
  flex-shrink: 1;
  overflow: hidden;
  @include highlight-tabbed;
}
.k-list-item-text {
  display: flex;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  align-items: baseline;
  width: 100%;
  line-height: 1.25rem;
  padding: .5rem .75rem;
}
.k-list-item-text em {
  font-style: normal;
  margin-right: 1rem;
  flex-grow: 1;
  min-width: 0;
  overflow: hidden;
  text-overflow: ellipsis;
  font-size: $font-size-small;
  color: $list-item-text-color;
}
.k-list-item-text small {
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
.k-list-item-options {
  position: relative;
  flex-shrink: 0;
}
.k-list-item-options .k-dropdown-content {
  top: $list-item-height;
}
.k-list-item-options > .k-button {
  height: $list-item-height;
  padding: 0 12px;
}
.k-list-item-options > .k-button .k-icon {
  height: $list-item-height;
}
</style>
