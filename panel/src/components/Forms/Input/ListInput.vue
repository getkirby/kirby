<template>
  <k-writer
    ref="input"
    :extensions="extensions"
    :marks="marks"
    :nodes="['bulletList', 'orderedList']"
    :value="value"
    class="k-list-input"
    @input="$emit('input', $event)"
  />
</template>

<script>
import ListDoc from "@/components/Writer/Nodes/ListDoc";

export default {
  inheritAttrs: false,
  props: {
    marks: {
      type: [Array, Boolean],
      default: true
    },
    value: String
  },
  computed: {
    extensions() {
      return [new ListDoc()];
    }
  },
  methods: {
    focus() {
      this.$refs.input.focus();
    }
  }
};
</script>

<style lang="scss">
.k-list-input .ProseMirror {
  line-height: 1.5em;
}
.k-list-input .ProseMirror ol > li::marker {
  font-size: $text-sm;
  color: $color-gray-500;
}
</style>
