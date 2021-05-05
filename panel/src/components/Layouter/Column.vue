<template>
  <div
    :id="id"
    :data-width="width"
    tabindex="0"
    class="k-column k-layout-column"
    @dblclick="$refs.blocks.choose(blocks.length)"
  >
    <k-blocks
      ref="blocks"
      :endpoints="endpoints"
      :fieldset-groups="fieldsetGroups"
      :fieldsets="fieldsets"
      :value="blocks"
      group="layout"
      @input="$emit('input', $event)"
      @dblclick.native.stop
    />
  </div>
</template>

<script>
/**
 * @displayName LayoutColumn
 * @internal
 */
export default {
  props: {
    blocks: Array,
    endpoints: Object,
    fieldsetGroups: Object,
    fieldsets: Object,
    id: String,
    isSelected: Boolean,
    width: String,
  }
};
</script>

<style>
.k-layout-column {
  position: relative;
  height: 100%;
  display: flex;
  flex-direction: column;
  background: var(--color-white);
  min-height: 6rem;
}
.k-layout-column:focus {
  outline: 0;
}
.k-layout-column .k-blocks {
  background: none;
  box-shadow: none;
  padding: 0;
  height: 100%;
  background: var(--color-white);
}
.k-layout-column .k-blocks-list {
  display: flex;
  flex-direction: column;
  height: 100%;
}
.k-layout-column .k-blocks .k-block-container:last-of-type {
  flex-grow: 1;
}
.k-layout-column .k-blocks .k-empty {
  position: absolute;
  top: 0;
  left: 0;
  bottom: 0;
  right: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 0;
  opacity: 0;
  transition: opacity .3s;
}
.k-layout-column .k-blocks .k-empty:hover {
  opacity: 1;
}
.k-layout-column .k-blocks .k-empty .k-icon {
  border-right: 0;
}
</style>
