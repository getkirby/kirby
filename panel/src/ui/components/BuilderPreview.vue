<template>
  <k-drawer
    ref="drawer"
    :title="`Preview / ${field} / ${title}`"
    flow="vertical"
  >
    <iframe class="k-builder-preview bg-yellow" />
  </k-drawer>
</template>

<script>

export default {
  props: {
    field: String
  },
  data() {
    return {
      block: {},
      fieldset: {}
    };
  },
  computed: {
    title() {
      return this.$helper.string.template(
        this.fieldset.label || this.fieldset.name || "",
        this.block.value
      );
    }
  },
  methods: {
    open(params) {
      this.block = params.block;
      this.fieldset = params.fieldset;
      this.$refs.drawer.open();
    }
  }
}
</script>

<style lang="scss">
.k-builder-preview {
  width: 100%;
  border: 0;
}
</style>
