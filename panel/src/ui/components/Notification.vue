<template>
  <div
    v-if="message"
    :data-theme="type"
    class="k-notification flex flex-shrink-0 items-center bg-black text-white text-sm"
  >
    <p
      class="flex-grow py-2 px-3"
      @click="$emit('click')"
      v-html="message"
    />
    <k-button
      icon="cancel"
      class="flex py-2 px-3"
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
.k-notification[data-theme="info"] {
  background-color: $color-blue-300;
  color: $color-black;
}
.k-notification[data-theme="success"] {
  background-color: $color-green-300;
  color: $color-black;
}
.k-notification p {
  word-wrap: break-word;
  overflow: hidden;
  line-height: 1.35em;
}
.k-notification .k-button {
  height: 2rem;
}
</style>
