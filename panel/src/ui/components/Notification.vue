<template>
  <div
    v-if="message"
    :data-theme="type"
    class="k-notification flex flex-shrink-0 items-center bg-black text-white text-sm"
  >
    <p class="flex-grow px-3">{{ message }}</p>
    <k-button
      icon="cancel"
      class="flex ml-4 p-3"
      @click="$emit('close')"
    />
  </div>
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    message: String,
    /**
     * Available options: `error`|`success`
     */
    type: String
  }
};
</script>

<style lang="scss">
.k-notification[data-theme="error"] {
  background-color: $color-red-300;
  color: $color-black;
}
.k-notification[data-theme="success"] {
  background-color: $color-green-300;
  color: $color-black;
}
.k-notification p {
  word-wrap: break-word;
  overflow: hidden;
  line-height: 1.25em;
}
</style>
