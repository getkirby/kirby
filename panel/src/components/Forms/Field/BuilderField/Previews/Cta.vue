<template>
  <div class="k-block-cta" @click="$emit('edit')">{{ content.text }}</div>
</template>

<script>
export default {
  props: {
    content: Object
  }
};
</script>

<style lang="scss">
.k-block-cta {
  border: 2px solid #000;
  display: inline-flex;
  padding: .25rem 1rem;
  border-radius: 3rem;
  cursor: pointer;
  margin: .75rem 0;
  font-size: $text-sm;
  font-weight: $font-bold;
}
</style>
