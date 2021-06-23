<template>
  <article
    :class="layout ? 'k-' + layout + '-item' : false"
    :data-has-figure="Boolean(image)"
    :data-has-info="Boolean(info)"
    :data-has-label="Boolean(label)"
    :data-has-options="Boolean(options)"
    class="k-item"
    tabindex="-1"
    @click="$emit('click', $event);"
    @dragstart="$emit('drag', $event)"
  >
    <!-- Image -->
    <k-item-image
      :image="image"
      :layout="layout"
      :width="width"
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
          <!-- eslint-disable-next-line vue/no-v-html -->
          <span v-html="title" />
        </k-link>
        <!-- eslint-disable-next-line vue/no-v-html -->
        <span v-else v-html="title" />
      </h3>
      <!-- eslint-disable-next-line vue/no-v-html -->
      <p v-if="info" class="k-item-info" v-html="info" />
    </header>

    <!-- Footer -->
    <footer
      v-if="flag || options || $slots.options"
      class="k-item-footer"
    >
      <nav
        class="k-item-buttons"
        @click.stop
      >
        <!-- Status icon -->
        <k-status-icon
          v-if="flag"
          v-bind="flag"
        />

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
    layout: {
      type: String,
      default: "list"
    },
    image: [Object, Boolean],
    info: String,
    label: String,
    link: {
      type: [Boolean, String, Function]
    },
    options: {
      type: [Array, Function]
    },
    sortable: Boolean,
    flag: Object,
    target: String,
    text: String,
    width: String
  },
  computed: {
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
}
.k-item:hover .k-item-sort-handle {
  opacity: 1;
}
.k-item-content {
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
  color: var(--color-gray-500);
  grid-area: info;
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
  z-index: 2;
}
.k-item-buttons > .k-button,
.k-item-buttons > .k-dropdown  {
  position: relative;
  width: 38px;
  height: 38px;
  display: flex !important;
  align-items: center;
  justify-content: center;
  line-height: 1;
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
  inset-inline-start: -1.5rem;
  width: 1.5rem;
}
.k-list-item .k-item-figure {
  width: 38px;
  border-start-start-radius: var(--rounded-sm);
  border-start-end-radius: var(--rounded-sm);
}
.k-list-item .k-item-content {
  display: flex;
  overflow: hidden;
  flex-grow: 1;
  flex-shrink: 2;
  justify-content: space-between;
  align-items: center;
  margin-inline-start: .75rem;
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
  margin-inline-end: .5rem;
}
.k-list-item .k-item-footer {
  flex-shrink: 0;
}
.k-list-item .k-item-buttons {
  flex-shrink: 0;
}
.k-list-item .k-item-label {
  margin-inline-end: .5rem;
}
.k-list-item .k-item-info {
  justify-self: end;
}


/** Cardlet Item **/
.k-cardlets-item {
  display: grid;
  height: 6rem;
  grid-template-columns: auto;
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
.k-cardlets-item .k-item-sort-handle {
  margin: .25rem;
  background: var(--color-background);
  box-shadow: var(--shado-md);
}
.k-cardlets-item .k-item-figure {
  grid-area: figure;
  border-start-start-radius: var(--rounded-sm);
  border-end-start-radius: var(--rounded-sm);
}
.k-cardlets-item .k-item-content {
  padding: .5rem .75rem;
  grid-area: content;
}
.k-cardlets-item .k-item-footer {
  grid-area: footer;
  padding-block: .5rem;
}
.k-cardlets-item .k-item-label {
  margin-inline-start: -2px;
  margin-inline-end: .5rem;
}


/** Card Item **/
.k-cards-item {
  display: grid;
  grid-template-columns: auto;
  grid-template-rows: auto auto auto;
  grid-template-areas:
    "figure"
    "content"
    "footer";
}
.k-cards-item .k-item-sort-handle {
  margin: .25rem;
  background: var(--color-background);
  box-shadow: var(--shadow-md);
}
.k-cards-item .k-item-figure {
  grid-area: figure;
  border-start-start-radius: var(--rounded-sm);
  border-start-end-radius: var(--rounded-sm);
}
.k-cards-item .k-item-content {
  grid-area: content;
  padding: .5rem .75rem;
  overflow: hidden;
}
.k-cards-item .k-item-title,
.k-cards-item .k-item-info {
  white-space: normal;
  word-wrap: break-word;
}
.k-cards-item .k-item-info {
  padding-block-start: .125rem;
}
.k-cards-item .k-item-footer {
  grid-area: footer;
  width: auto;
  padding-inline-start: .7rem;
}
.k-cards-item .k-item-label {
  margin-inline-start: -2px;
  margin-inline-end: .5rem;
}
.k-cards-item:not([data-has-label]) {
  grid-template-columns: auto auto;
  grid-template-rows: auto 1fr;
  grid-template-areas:
    "figure figure"
    "content footer";
}
.k-cards-item:not([data-has-label]) .k-item-footer {
  align-items: flex-end;
  padding-left: 0;
}
</style>
