<template>
  <div
    v-if="message"
    :data-theme="type"
    class="k-notification"
  >
    <p>{{ message }}</p>
    <k-button
      icon="cancel"
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
.k-notification {
  padding: 0.625rem 1rem;
  background: $color-black;
  width: 100%;
  line-height: 1.25rem;
  color: $color-white;
  display: flex;
  flex-shrink: 0;
  align-items: center;
}
.k-notification[data-theme="error"] {
  background: $color-red-300;
  color: $color-black;
}
.k-notification[data-theme="success"] {
  background: $color-green-300;
  color: $color-black;
}
.k-notification p {
  flex-grow: 1;
  word-wrap: break-word;
  overflow: hidden;
}
.k-notification .k-button {
  display: flex;
  margin-left: 1rem;
}
</style>
