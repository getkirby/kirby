<template>
  <component :is="view" v-bind="props" />
</template>

<script>

export default {
  props: {
    props: Object,
    id: String,
  },
  computed: {
    view() {
      return "k-" + this.id + "-plugin-view";
    }
  }
};
</script>
