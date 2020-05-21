<template>
  <article
    :class="[layout ? 'k-' + layout + '-item' : false, className]"
    :data-has-flag="Boolean(flag)"
    :data-has-figure="Boolean(image) || Boolean(icon)"
    :data-has-info="Boolean(info)"
    :data-has-label="Boolean(label)"
    :data-has-options="Boolean(options)"
    :style="styles"
    class="k-item"
    tabindex="-1"
    @click="onClick"
    @dragstart="onDragStart"
  >
    <!-- Figure -->
    <k-item-figure
      :icon="icon"
      :image="image"
      :layout="layout"
    />

    <!-- Sort handle -->
    <k-sort-handle
      v-if="sortable"
      class="k-item-sort-handle"
    />

    <!-- Content -->
    <header class="k-item-content">
      <h3 class="k-item-title">
        <k-link
          v-if="link"
          :target="target"
          :to="link"
          class="k-item-title-link"
        >
          <span v-html="heading" />
        </k-link>
        <span
          v-else
          v-html="heading"
        />
      </h3>
      <p
        v-if="info"
        class="k-item-info"
        v-html="info"
      />
    </header>

    <!-- Footer -->
    <footer
      v-if="options || flag"
      class="k-item-footer"
    >
      <nav
        v-if="flag || options"
        class="k-item-buttons"
        @click.stop
      >
        <!-- Flag -->
        <slot name="flag">
          <k-button
            v-if="flag"
            :icon="flag.icon"
            :color="flag.color"
            :class="flag.class"
            :disabled="flag.disabled"
            class="k-item-button k-item-flag-button"
            @click="onFlag"
          />
        </slot>

        <!-- Options -->
        <slot name="options">
          <k-options-dropdown
            v-if="options"
            :options="options"
            class="k-item-options-dropdown"
            @option="onOption"
          />
        </slot>
      </nav>
    </footer>
  </article>
</template>

<script>
import figure from "@/ui/mixins/figure.js";

export default {
  inheritAttrs: false,
  mixins: [figure],
  props: {
    className: String,
    dragText: String,
    flag: {
      type: [Boolean, Object],
      default: false,
    },
    info: [Boolean, String],
    label: String,
    link: {
      type: [Boolean, String, Function],
      default: false,
    },
    options: {
      type: [Boolean, Array, Function],
      default: false
    },
    sortable: {
      type: Boolean,
      default: false
    },
    styles: [String, Object],
    target: {
      type: [Boolean, String],
    },
    text: String,
    title: String,
  },
  computed: {
    heading() {
      return this.title || this.text || "-";
    }
  },
  methods: {
    onClick() {
      this.$emit("click", event);
    },
    onDragStart(event) {
      this.$store.dispatch("drag", {
        type: "text",
        data: this.dragText
      });
    },
    onFlag(event) {
      this.$emit("flag", event);
    },
    onOption(event) {
      this.$emit("action", event);
      this.$emit("option", event);
    }
  }
};
</script>

<style lang="scss">
/** Shared item styles **/
.k-item {
  position: relative;
  background: $color-white;
  border-radius: $rounded-sm;
  box-shadow: $shadow;
  display: grid;
  line-height: 1;
}
.k-item:focus {
  outline: 0;
}
.k-item:focus-within {
  box-shadow: $shadow-outline;
}
.k-item a:focus {
  outline: 0;
}
.k-item-sort-handle.k-sort-handle {
  position: absolute;
  opacity: 0;
  width: 1.25rem;
  height: 1.5rem;
  z-index: 2;
}
.k-item:hover .k-item-sort-handle {
  opacity: 1;
}
.k-item-content {
  overflow: hidden;
}
.k-item-title,
.k-item-info {
  font-size: $text-sm;
  font-weight: normal;
  text-overflow: ellipsis;
  white-space: nowrap;
  line-height: 1.125rem;
  overflow: hidden;
}
.k-item-info {
  color: $color-gray-700;
  grid-area: info;
}
.k-item-title-link.k-link[data-tabbed] {
  box-shadow: none;
}
.k-item-title-link::after {
  position: absolute;
  content: "";
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  z-index: 1;
}
.k-item-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  min-width: 0;
}
.k-item-buttons {
  position: relative;
  display: flex;
  justify-content: flex-end;
  flex-shrink: 0;
  flex-grow: 1;
}
.k-item-flag-button,
.k-item-options-dropdown {
  position: relative;
  width: 38px;
  height: 38px;
  display: flex;
  align-items: center;
  justify-content: center;
  line-height: 1;
}
.k-item-flag-button {
  z-index: 1;
}
.k-item-options-dropdown {
  z-index: 2;
}

/** List Item **/
.k-list-item {
  display: flex;
  align-items: center;
  height: 38px;
}
.k-list-item .k-sort-handle {
  position: absolute;
  left: -1.5rem;
  width: 1.5rem;
}
.k-list-item .k-item-figure {
  width: 38px;
  border-top-left-radius: $rounded-sm;
  border-bottom-left-radius: $rounded-sm;
}
.k-list-item .k-item-content {
  display: flex;
  overflow: hidden;
  flex-grow: 1;
  flex-shrink: 2;
  justify-content: space-between;
  align-items: center;
  margin-left: .75rem;
}
.k-list-item .k-item-title,
.k-list-item .k-item-info {
  flex-grow: 1;
  overflow: hidden;
  text-overflow: ellipsis;
  line-height: 1.5rem;
}
.k-list-item .k-item-title {
  margin-right: .5rem;
  flex-shrink: 1;
}
.k-list-item .k-item-info {
  flex-shrink: 2;
  text-align: right;
  margin-right: .5rem;
}
.k-list-item .k-item-footer {
  flex-shrink: 0;
}
.k-list-item .k-item-buttons {
  flex-shrink: 0;
}
.k-list-item .k-item-label {
  margin-right: .5rem;
}
.k-list-item .k-item-info {
  justify-self: end;
}


/** Cardlet Item **/
.k-cardlet-item {
  display: grid;
  height: 6rem;
  grid-template-columns: auto;
  grid-template-rows: auto 38px;
  grid-template-areas:
    "content"
    "footer";
}
.k-cardlet-item[data-has-figure] {
  grid-template-columns: 6rem auto;
  grid-template-areas:
    "figure content"
    "figure footer";
}
.k-cardlet-item .k-item-sort-handle {
  margin: .25rem;
  background: $color-background;
  box-shadow: $shadow-md;
}
.k-cardlet-item .k-item-figure {
  grid-area: figure;
  border-top-left-radius: $rounded-sm;
  border-bottom-left-radius: $rounded-sm;
}
.k-cardlet-item .k-item-content {
  padding: .5rem .75rem;
  grid-area: content;
}
.k-cardlet-item .k-item-footer {
  grid-area: footer;
  padding-left: .7rem;
}
.k-cardlet-item .k-item-label {
  margin-right: .5rem;
  margin-left: -2px;
}


/** Card Item **/
.k-card-item {
  display: grid;
  grid-template-columns: auto;
  grid-template-rows: auto auto auto;
  grid-template-areas:
    "figure"
    "content"
    "footer";
}
.k-card-item .k-item-sort-handle {
  margin: .25rem;
  background: $color-background;
  box-shadow: $shadow-md;
}
.k-card-item .k-item-figure {
  grid-area: figure;
  border-top-left-radius: $rounded-sm;
  border-top-right-radius: $rounded-sm;
}
.k-card-item .k-item-content {
  grid-area: content;
  padding: .5rem .75rem;
  overflow: hidden;
}
.k-card-item .k-item-title,
.k-card-item .k-item-info {
  white-space: normal;
  word-wrap: break-word;
}
.k-card-item .k-item-info {
  padding-top: .125rem;
}
.k-card-item .k-item-footer {
  grid-area: footer;
  width: auto;
  padding-left: .7rem;
}
.k-card-item .k-item-label {
  margin-left: -2px;
  margin-right: .5rem;
}
.k-card-item:not([data-has-label]) {
  grid-template-columns: auto auto;
  grid-template-rows: auto 1fr;
  grid-template-areas:
    "figure figure"
    "content footer";
}
.k-card-item:not([data-has-label]) .k-item-footer {
  align-items: flex-end;
  padding-left: 0;
}
</style>
