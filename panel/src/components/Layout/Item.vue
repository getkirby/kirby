<template>
  <article
    :class="layout ? 'k-' + layout + '-item' : false"
    v-bind="data"
    :data-has-figure="hasFigure"
    :data-has-info="Boolean(info)"
    :data-has-label="Boolean(label)"
    :data-has-options="Boolean(options)"
    class="k-item"
    tabindex="-1"
    @click="$emit('click', $event)"
    @dragstart="$emit('drag', $event)"
  >
    <!-- Image -->
    <slot name="image">
      <k-item-image
        v-if="hasFigure"
        :image="image"
        :layout="layout"
        :width="width"
      />
    </slot>

    <!-- Sort handle -->
    <k-sort-handle v-if="sortable" class="k-item-sort-handle" />

    <!-- Content -->
    <header class="k-item-content">
      <slot>
        <h3 class="k-item-title">
          <k-link
            v-if="link"
            :target="target"
            :to="link"
            class="k-item-title-link"
          >
            <!-- eslint-disable-next-line vue/no-v-html -->
            <span v-html="title" />
          </k-link>
          <!-- eslint-disable-next-line vue/no-v-html -->
          <span v-else v-html="title" />
        </h3>
        <!-- eslint-disable-next-line vue/no-v-html -->
        <p v-if="info" class="k-item-info" v-html="info" />
      </slot>
    </header>

    <!-- Footer -->
    <footer v-if="flag || options || $slots.options" class="k-item-footer">
      <nav class="k-item-buttons" @click.stop>
        <!-- Status icon -->
        <k-status-icon v-if="flag" v-bind="flag" />

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
export default {
  inheritAttrs: false,
  props: {
    data: Object,
    flag: Object,
    image: [Object, Boolean],
    info: String,
    label: String,
    layout: {
      type: String,
      default: "list"
    },
    link: {
      type: [Boolean, String, Function]
    },
    options: {
      type: [Array, Function, String]
    },
    sortable: Boolean,
    target: String,
    text: String,
    width: String
  },
  computed: {
    hasFigure() {
      return this.image !== false && Object.keys(this.image).length > 0;
    },
    title() {
      return this.text || "-";
    }
  },
  methods: {
    onOption(event) {
      this.$emit("action", event);
      this.$emit("option", event);
    }
  }
};
</script>

<style>
/** Shared item styles **/
.k-item {
  position: relative;
  background: var(--color-white);
  border-radius: var(--rounded-sm);
  box-shadow: var(--shadow);
  display: grid;
  grid-template-columns: auto;
  line-height: 1;
}
.k-item:focus {
  outline: 0;
}
.k-item:focus-within {
  box-shadow: var(--shadow-outline);
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
  border-radius: 1px;
}
.k-item:hover .k-item-sort-handle {
  opacity: 1;
}
.k-item-figure {
  grid-area: figure;
}
.k-item-content {
  grid-area: content;
  overflow: hidden;
}
.k-item-title,
.k-item-info {
  font-size: var(--text-sm);
  font-weight: normal;
  text-overflow: ellipsis;
  white-space: nowrap;
  line-height: 1.125rem;
  overflow: hidden;
}
.k-item-info {
  grid-area: info;
  color: var(--color-gray-500);
}
.k-item-title-link.k-link[data-tabbed] {
  box-shadow: none;
}
.k-item-title-link::after {
  position: absolute;
  content: "";
  inset: 0;
  z-index: 1;
}
.k-item-footer {
  grid-area: footer;
  display: flex;
  justify-content: space-between;
  align-items: center;
  min-width: 0;
}
.k-item-label {
  margin-inline-end: 0.5rem;
}
.k-item-buttons {
  position: relative;
  display: flex;
  justify-content: flex-end;
  flex-shrink: 0;
  flex-grow: 1;
}
.k-item-buttons > .k-button,
.k-item-buttons > .k-dropdown {
  position: relative;
  width: 38px;
  height: 38px;
  display: flex !important;
  align-items: center;
  justify-content: center;
  line-height: 1;
}
.k-item-buttons > .k-button {
  z-index: 1;
}
.k-item-buttons > .k-dropdown {
  z-index: var(--z-dropdown);
}

/** List Item **/
.k-list-item {
  display: flex;
  align-items: center;
  height: 38px;
}
.k-list-item .k-item-sort-handle {
  inset-inline-start: -1.5rem;
  width: 1.5rem;
}
.k-list-item .k-item-figure {
  width: 38px;
  border-start-start-radius: var(--rounded-sm);
  border-end-start-radius: var(--rounded-sm);
}
.k-list-item .k-item-content {
  display: flex;
  flex-grow: 1;
  flex-shrink: 2;
  justify-content: space-between;
  align-items: center;
  margin-inline-start: 0.75rem;
}
.k-list-item .k-item-title,
.k-list-item .k-item-info {
  flex-grow: 1;
  line-height: 1.5rem;
}
.k-list-item .k-item-title {
  margin-inline-end: 0.5rem;
  flex-shrink: 1;
}
.k-list-item .k-item-info {
  flex-shrink: 2;
  text-align: end;
  justify-self: end;
  margin-inline-end: 0.5rem;
}
.k-list-item .k-item-footer {
  flex-shrink: 0;
}
.k-list-item .k-item-buttons {
  flex-shrink: 0;
}

/** Cardlet and card items shared */
.k-item:not(.k-list-item) .k-item-sort-handle {
  margin: 0.25rem;
  background: var(--color-background);
  box-shadow: var(--shadow-md);
}
.k-item:not(.k-list-item) .k-item-label {
  margin-inline-start: -2px;
}
.k-item:not(.k-list-item) .k-item-content {
  padding: 0.625rem 0.75rem;
}

/** Cardlet Item **/
.k-cardlets-item {
  height: 6rem;
  grid-template-rows: auto 38px;
  grid-template-areas:
    "content"
    "footer";
}
.k-cardlets-item[data-has-figure] {
  grid-template-columns: 6rem auto;
  grid-template-areas:
    "figure content"
    "figure footer";
}
.k-cardlets-item .k-item-figure {
  border-start-start-radius: var(--rounded-sm);
  border-end-start-radius: var(--rounded-sm);
}
.k-cardlets-item .k-item-footer {
  padding-block: 0.5rem;
}

/** Card Item **/
.k-cards-item {
  grid-template-rows: auto auto auto;
  grid-template-areas:
    "figure"
    "content"
    "footer";
}
.k-cards-item .k-item-figure {
  border-start-start-radius: var(--rounded-sm);
  border-start-end-radius: var(--rounded-sm);
}
.k-cards-item .k-item-content {
  overflow: hidden;
  align-self: center;
}
.k-cards-item[data-has-info] .k-item-content {
  align-self: flex-start;
}
.k-cards-item .k-item-title,
.k-cards-item .k-item-info {
  white-space: normal;
}
.k-cards-item .k-item-info {
  padding-top: 0.125rem;
}
.k-cards-item .k-item-footer {
  width: auto;
  padding-inline-start: 0.7rem;
}
.k-cards-item:not([data-has-label]) {
  grid-template-columns: 1fr auto;
  grid-template-rows: auto 1fr;
  grid-template-areas:
    "figure figure"
    "content footer";
}
.k-cards-item:not([data-has-label]) .k-item-footer {
  align-items: flex-end;
  padding-inline-start: 0;
}
</style>
