<template>
  <k-writer
    ref="input"
    v-model="list"
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
  data() {
    return {
      list: this.value
    };
  },
  computed: {
    extensions() {
      return [new ListDoc({
        inline: true
      })];
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
        this.$emit("input", this.list = "");
        return;
      }

      let text = list.textContent.trim();

      if (text.length === 0) {
        this.$emit("input", this.list = "");
        return;
      }

      // updates `list` data with raw html
      this.list = html;

      // emit value with removes `<p>` and `</p>` tags from html value
      this.$emit("input", html.replace(/(<p>|<\/p>)/gi, ""));
    }
  }
};
</script>

<style>
.k-list-input .ProseMirror {
  line-height: 1.5em;
}
.k-list-input .ProseMirror ol > li::marker {
  font-size: var(--text-sm);
  color: var(--color-gray-500);
}
</style>
