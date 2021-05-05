<template>
  <component :is="element" class="k-list-item" v-on="$listeners">
    <k-sort-handle v-if="sortable" />
    <k-link
      :to="link"
      :target="target"
      class="k-list-item-content"
    >
      <span v-if="image" class="k-list-item-image">
        <slot name="image">
          <k-image v-if="imageOptions" v-bind="imageOptions" />
          <k-icon v-else v-bind="icon" />
        </slot>
      </span>
      <span class="k-list-item-text">
        <slot>
          <!-- eslint-disable-next-line vue/no-v-html -->
          <em v-html="text" />
          <!-- eslint-disable-next-line vue/no-v-html -->
          <small v-if="info" v-html="info" />
        </slot>
      </span>
    </k-link>
    <nav class="k-list-item-options">
      <!-- @slot You can overwrite the options button and dropdown with your own elements -->
      <slot name="options">
        <k-button
          v-if="flag"
          v-bind="flag"
          class="k-list-item-status"
          @click="flag.click"
        />
        <k-status-icon
          v-else-if="statusIcon"
          v-bind="statusIcon"
          class="k-list-item-status"
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
    </nav>
  </component>
</template>

<script>
import previewThumb from "@/helpers/previewThumb.js";

/**
 * The ListItem component is a very flexible tool to display an image together with a title (text) some meta information (info) and a dropdown with options in a line. It's the counter part to our cards.
 * @internal
 */
export default {
  inheritAttrs: false,
  props: {
    element: {
      type: String,
      default: "li"
    },
    /**
     * Defines the list item image
     * @values See the props of `<k-image>`
     */
    image: [Object, Boolean],
    /**
     * Defines the list item icon instead of an image. If an image is still defined, the image will be used instead of the icon and this setting will be ignored.
     * @values See the props of `<k-icon>`
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
    sortable: Boolean,
    /**
     * Sets the list item text
     */
    text: String,
    /**
     * Sets the link target
     */
    target: String,
    /**
     * Sets the secondary info text
     */
    info: String,
    /**
     * Sets the link for the list item
     */
    link: [String, Function],
    /**
     * An additional button left next to the options toggle, which can be used to define any additional "flag" or option for the list item.
     */
    flag: Object,
    /**
     * Defines the options dropdown.
     */
    options: [Array, Function],
    /**
     * @ignore
     */
    statusIcon: Object,
  },
  computed: {
    imageOptions() {
      return previewThumb(this.image);
    }
  }
};
</script>

<style>
.k-list-item {
  --list-item-height: 38px;

  position: relative;
  display: flex;
  align-items: center;
  background: var(--color-white);
  border-radius: var(--rounded-xs);
  box-shadow: var(--shadow);
}
[data-disabled] .k-list-item {
  background: var(--color-background);
}
.k-list-item .k-sort-handle {
  position: absolute;
  left: -1.5rem;
  width: 1.5rem;
  height: var(--list-item-height);
  opacity: 0;
}
.k-list:hover .k-sort-handle {
  opacity: .25;
}
.k-list-item:hover .k-sort-handle {
  opacity: 1;
}
.k-list-item.k-sortable-ghost {
  position: relative;
  outline: 2px solid var(--color-focus);
  z-index: 1;
  box-shadow: rgba(17, 17, 17, .25) 0 5px 10px);
}
.k-list-item.k-sortable-fallback {
  opacity: .25 !important;
  overflow: hidden;
}
.k-list-item-image {
  width: var(--list-item-height);
  height: var(--list-item-height);
  overflow: hidden;
  flex-shrink: 0;
  line-height: 0;
}
.k-list-item-image .k-image {
  width: var(--list-item-height);
  height: var(--list-item-height);
  object-fit: contain;
}
.k-list-item-image .k-icon {
  width: var(--list-item-height);
  height: var(--list-item-height);
}
.k-list-item-image .k-icon svg {
  opacity: .5;
}
.k-list-item-content {
  display: flex;
  align-items: center;
  flex-grow: 1;
  flex-shrink: 1;
  overflow: hidden;
  outline: none;
}
.k-list-item-content[data-tabbed] {
  box-shadow: var(--shadow-outline);
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
  font-size: var(--text-sm);
  color: var(--color-gray-900);
}
.k-list-item-text small {
  color: var(--color-gray-500);
  font-size: var(--text-xs);
  font-variant-numeric: tabular-nums;
  color: var(--color-gray-600);
  display: none;
  min-width: 0;
  overflow: hidden;
  text-overflow: ellipsis;
}
@media screen and (min-width: 30em) {
  .k-list-item-text small {
    display: block;
  }
}
.k-list-item-status {
  height: auto !important;
}
.k-list-item-options {
  position: relative;
  flex-shrink: 0;
}
.k-list-item-options .k-dropdown-content {
  top: var(--list-item-height);
}
.k-list-item-options > .k-button {
  height: var(--list-item-height);
  padding: 0 12px;
}
.k-list-item-options > .k-button > .k-button-icon {
  height: var(--list-item-height);
}
</style>
