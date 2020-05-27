<template>
  <span
    :id="id"
    :data-disabled="true"
    :data-responsive="responsive"
    :data-theme="theme"
    :title="tooltip || text"
    class="k-button cursor-default"
  >
    <k-icon
      v-if="icon"
      :type="icon"
      :alt="tooltip"
      class="k-button-icon"
    />
    <span
      v-if="$slots.default"
      class="k-button-text"
    ><slot /></span>
  </span>
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    icon: [String, Object],
    id: [String, Number],
    responsive: Boolean,
    text: String,
    theme: String,
    tooltip: String,
  }
};
</script>

<style lang="scss">
.k-button[data-disabled] {
  opacity: 0.5;
}
</style>
