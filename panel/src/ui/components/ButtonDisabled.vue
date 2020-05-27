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
      v-bind="iconOptions"
      class="k-button-icon"
    />
    <span
      v-if="$slots.default"
      class="k-button-text"
    ><slot /></span>
  </span>
</template>

<script>
import ButtonNative from "./ButtonNative.vue";

export default {
  extends: ButtonNative,
  inheritAttrs: false,
};
</script>

<style lang="scss">
.k-button[data-disabled] {
  opacity: 0.5;
}
</style>
