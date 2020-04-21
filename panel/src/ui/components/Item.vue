<template>
  <article
    :class="layout ? 'k-' + layout + '-item' : false"
    :data-has-flag="Boolean(flag)"
    :data-has-figure="Boolean(image) || Boolean(icon)"
    :data-has-info="Boolean(info)"
    :data-has-label="Boolean(label)"
    :data-has-options="Boolean(options)"
    class="k-item"
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
        <template v-if="link">
          <k-link
            :target="link"
            :to="link"
            class="k-item-title-link"
          >
            {{ heading }}
          </k-link>
        </template>
        <template v-else>
          {{ heading }}
        </template>
      </h3>
      <p
        v-if="info"
        class="k-item-info"
      >
        {{ info }}
      </p>
    </header>

    <!-- Footer -->
    <footer
      v-if="options || flag"
      class="k-item-footer"
    >
      <nav
        class="k-item-buttons"
        v-if="flag || options"
      >
        <!-- Flag -->
        <slot name="flag">
          <k-button
            v-if="flag"
            :icon="flag.icon"
            :color="flag.color"
            class="k-item-button k-item-flag-button"
            @click="onFlag"
          />
        </slot>

        <!-- Options -->
        <slot name="options">
          <k-options-dropdown
            :options="options"
            @option="onOption"
          />
        </slot>
      </nav>
    </footer>
  </article>
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    dragText: String,
    flag: {
      type: [Boolean, Object],
      default: false,
    },
    icon: {
      type: [Object, Boolean],
      default: true,
    },
    image: {
      type: [Object, Boolean],
      default: true,
    },
    info: String,
    layout: {
      type: String,
      default: "list",
    },
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
  line-height: 1.25rem;
  overflow: hidden;
}
.k-item-info {
  color: $color-dark-grey;
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
.k-item-label {
  display: flex;
  flex-shrink: 1;
  min-width: 0;
  height: 38px;
  align-items: center;
}
.k-item-label-button.k-button {
  font-size: $text-xs;
  font-family: $font-mono;
  display: flex;
  padding: .125rem .5rem;
  border: 2px solid $color-border;
  border-radius: 2rem;
  white-space: nowrap;
  max-width: 100%;
  z-index: 2;
}
.k-item-label .k-button-text {
  overflow: hidden;
  text-overflow: ellipsis;
}
.k-item-buttons {
  position: relative;
  display: flex;
  justify-content: flex-end;
  flex-shrink: 0;
  flex-grow: 1;
}
.k-item-button.k-button,
.k-item-buttons .k-options-dropdown {
  width: 38px;
  height: 38px;
  display: flex;
  align-items: center;
  justify-content: center;
  line-height: 1;
  z-index:  2;
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
  grid-template-rows: auto auto;
  grid-template-areas:
    "figure figure"
    "content footer";
}
.k-card-item:not([data-has-label]) .k-item-footer {
  align-items: flex-end;
  padding-left: 0;
}

</style>
