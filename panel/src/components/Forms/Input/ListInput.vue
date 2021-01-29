<template>
  <k-writer
    ref="input"
    v-bind="$props"
    :extensions="extensions"
    :nodes="['bulletList', 'orderedList']"
    class="k-list-input"
    @input="onInput"
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
    },
    onInput(html) {
      let dom  = new DOMParser().parseFromString(html, "text/html");
      let list = dom.querySelector('ul, ol');

      if (!list) {
        this.$emit("input", "");
        return;
      }

      let text = list.textContent.trim();

      if (text.length === 0) {
        this.$emit("input", "");
        return;
      }

      this.$emit("input", html);
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
