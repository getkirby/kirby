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
        <!-- eslint-disable-next-line vue/no-v-html -->
        <span v-if="info" class="k-card-info" v-html="info" />
      </figcaption>
    </component>

    <nav class="k-card-options">
      <!-- @slot You can overwrite the options button and dropdown with your own elements -->
      <slot name="options">
        <k-button
          v-if="flag"
          v-bind="flag"
          class="k-card-options-button"
          @click="flag.click"
        />
        <k-status-icon
          v-else-if="statusIcon"
          v-bind="statusIcon"
          class="k-card-options-button"
        />
        <k-button
          v-if="options"
          :tooltip="$t('options')"
          icon="dots"
          class="k-card-options-button"
          @click.stop="$refs.dropdown.toggle()"
        />
        <!--
          If you've defined the options dropdown, each click on a dropdown item will emmit the `action` event with the option's object's `click` value.
          @event action
          @example {icon: "edit", text: "Edit", click: "edit"} => "edit"
          @property {object} click
        -->
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
import previewThumb from "@/helpers/previewThumb.js";

/**
 * The Card component is a very flexible tool to display an image together with a title (text) some meta information (info) and a dropdown with options.
 */
export default {
  inheritAttrs: false,
  props: {
    column: String,
    /**
     * An additional button left next to the options toggle, which can be used to define any additional "flag" or option for the card.
     * @example { icon: 'trash', click: someClickHandler }
     */
    flag: Object,
    /**
     * Defines the card icon instead of an image. If an image is still defined, the image will be used instead of the icon and this setting will be ignored.
     * @example { type: 'file', back: 'black' }
     */
    icon: {
      type: Object,
      default() {
        return {
          type: "file",
          back: "black"
        };
      }
    },
    /**
     * Defines the card image
     * @example { url: 'https://picsum.photos/400/400?random', ratio: '1/1', back: 'pattern', cover: true }
     */
    image: [Object, Boolean],
    /**
     * Sets the secondary info text
     */
    info: String,
    /** 
     * Sets the link for the entire card
     */
    link: [String, Function],
    /** 
     * Defines the options dropdown
     * @example [ {icon: 'edit', text: 'Edit'}, {icon: 'trash', text: 'Delete'} ]
     */
    options: [Array, Function],
    sortable: Boolean,
    statusIcon: Object,
    /**
     * Sets the link target
     */
    target: String,
    /**
     * Sets the primary card text
     */
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
      return previewThumb(this.image, "cards", this.column);
    }
  }
};
</script>

<style>
.k-card {
  position: relative;
  min-width: 0;
  background: var(--color-white);
  border-radius: var(--rounded-xs);
  box-shadow: var(--shadow);
}
.k-card a {
  min-width: 0;
  background: var(--color-white);
}
.k-card:focus-within {
  box-shadow: var(--color-focus) 0 0 0 2px;
}
.k-card a:focus {
  outline: 0;
}

.k-card .k-sort-handle {
  position: absolute;
  top: .75rem;
  width: 2rem;
  height: 2rem;
  border-radius: var(--rounded-xs);
  background: var(--color-white);
  opacity: 0;
  color: var(--color-gray-900);
  z-index: 1;
  will-change: opacity;
  transition: opacity .3s;
}
[dir="ltr"] .k-card .k-sort-handle {
  right: .75rem;
}
[dir="rtl"] .k-card .k-sort-handle {
  left: .75rem;
}
.k-cards:hover .k-sort-handle {
  opacity: .25;
}
.k-card:hover .k-sort-handle {
  opacity: 1;
}

.k-card.k-sortable-ghost {
  outline: 2px solid var(--color-focus);
  border-radius: 0;
}

.k-card-image,
.k-card-icon {
  border-top-left-radius: var(--rounded-xs);
  border-top-right-radius: var(--rounded-xs);
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
  border-bottom-left-radius: var(--rounded-xs);
  border-bottom-right-radius: var(--rounded-xs);
  min-height: 2.25rem;
  padding: .5rem .75rem;
  overflow-wrap: break-word;
  word-wrap: break-word;
}

.k-card-text {
  display: block;
  font-weight: var(--font-normal);
  text-overflow: ellipsis;
  font-size: var(--text-sm);
}
.k-card-text[data-noinfo]:after {
  content: " ";
  height: 1em;
  width: 5rem;
  display: inline-block;
}
.k-card-info {
  color: var(--color-gray-600);
  display: block;
  font-size: var(--text-sm);
  text-overflow: ellipsis;
  overflow: hidden;
}
[dir="ltr"] .k-card-info {
  margin-right: 4rem;
}
[dir="rtl"] .k-card-info {
  margin-left: 4rem;
}

.k-card-options {
  position: absolute;
  bottom: 0;
}
[dir="ltr"] .k-card-options {
  right: 0;
}
[dir="rtl"] .k-card-options {
  left: 0;
}
.k-card-options > .k-button {
  position: relative;
  float: left;
  height: 2.25rem;
  padding: 0 .75rem;
  line-height: 1;
}
.k-card-options-dropdown {
  top: 2.25rem;
}
</style>
