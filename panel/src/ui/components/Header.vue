<template>
  <header
    :data-editable="editable"
    class="k-header"
  >
    <k-headline
      tag="h1"
      size="huge"
      class="mb-2"
    >
      <span
        v-if="editable && $listeners.edit"
        class="k-headline-editable cursor-pointer"
        @click="onClick"
      >
        <!-- @slot Use the default slot to inject the headline -->
        <slot />
        <k-icon type="edit" />
      </span>
      <slot v-else />
    </k-headline>
    <k-bar
      v-if="$slots.left || $slots.right"
      class="k-header-buttons"
    >
      <!-- @slot The left slot is normaly used to inject option buttons with a button group -->
      <slot
        slot="left"
        name="left"
        class="k-header-left"
      />
      <!-- @slot The right slot is perfect for `PrevNext` navigation or additional options -->
      <slot
        slot="right"
        name="right"
        class="k-header-right"
      />
    </k-bar>
    <k-tabs
      :tab="tab"
      :tabs="tabs"
    />
  </header>
</template>

<script>

export default {
  props: {
    /**
     * Shows an edit icon next to the headline
     * and emits an edit event on click on the headline
     */
    editable: {
      type: Boolean,
      default: false,
    },
    /**
     * An array of tab definitions to add tabs to the header
     */
    tabs: Array,
    /**
     * The name/id of the currently active tab
     */
    tab: String
  },
  methods: {
    onClick() {
      /**
       * Headline with edit icon has been clicked.
       */
      this.$emit('edit');
    }
  }
}
</script>

<style lang="scss">
.k-header {
  border-bottom: 1px solid $color-border;
  margin-bottom: 2rem;
  padding-top: 4vh;
}
.k-header .k-headline {
  min-height: 1.25em;
  word-wrap: break-word;
}
.k-header .k-header-buttons {
  margin-top: -.5rem;
  height: 3.25rem;
}
.k-header .k-headline-editable .k-icon {
  color: $color-gray-500;
  opacity: 0;
  transition: opacity .3s;
  display: inline-block;

  [dir="ltr"] & {
    margin-left: .5rem;
  }

  [dir="rtl"] & {
    margin-right: .5rem;
  }
}
.k-header .k-headline-editable:hover .k-icon {
  opacity: 1;
}
</style>
