<template>
  <k-inside>
    <component :is="view" />
  </k-inside>
</template>

<script>
export default {
  props: {
    id: String
  },
  computed: {
    view() {
      return "k-" + this.id + "-plugin-view";
    }
  }
};
</script>
