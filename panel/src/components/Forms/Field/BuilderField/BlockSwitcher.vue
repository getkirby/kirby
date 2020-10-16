<template>
  <component
    v-if="!isEditing"
    :is="previewComponent"
    v-bind="$props"
    @edit="isEditing = true"
    @update="$emit('update', $event)"
  />
  <k-block-generic
    v-else
    :sticky="true"
    v-bind="$props"
    @close="isEditing = false"
    @update="$emit('update', $event)"
  />
</template>

<script>
import Generic from "./Block/Generic.vue";

export default {
  props: Generic.props,
  data() {
    return {
      isEditing: false
    }
  },
  computed: {
    previewComponent() {
      return "k-block-" + this.type + "-preview";
    }
  }
};
</script>
