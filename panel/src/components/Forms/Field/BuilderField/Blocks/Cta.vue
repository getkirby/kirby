<template>
  <div @click="$emit('edit')" class="k-block-cta-button">{{ content.text }}</div>
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    content: Object
  },
  mounted() {
    if (this.content.text.length === 0) {
      this.$emit("edit");
    }
  }
};
</script>

<style lang="scss">
.k-block-cta {
  padding: 1.5rem 0;
}
.k-block-cta-button {
  border: 2px solid #000;
  display: inline-flex;
  padding: .25rem 1rem;
  border-radius: 3rem;
  cursor: pointer;
  font-size: $text-sm;
  font-weight: $font-bold;
}
</style>
