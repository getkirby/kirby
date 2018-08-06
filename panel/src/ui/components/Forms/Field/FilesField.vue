<template>
  <k-field v-bind="$props" class="k-files-field">
    <k-button slot="options" icon="add">Select file</k-button>

    <k-files-dialog>
    </k-files-dialog>

  </k-field>
</template>

<script>
import Field from "../Field.vue";

export default {
  inheritAttrs: false,
  props: {
    ...Field.props
  },
  data() {
    return {
      selected: this.value
    };
  },
  watch: {
    value(value) {
      this.selected = value;
    }
  },
  methods: {
    focus() {
      this.$refs.input.focus();
    }
  }
}
</script>

<style lang="scss">

.k-files-picker .k-dialog-box {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
}
.k-files-picker .k-dialog-body {
  max-height: none;
  flex-grow: 1;
}

</style>
