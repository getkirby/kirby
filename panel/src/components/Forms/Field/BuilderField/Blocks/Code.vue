<template>
  <div>
  <div class="k-block-code-editor">
    <k-editor
      placeholder="Your code …"
      :value="content.code"
      @input="update({ code: $event })"
    />

    <div class="k-block-code-editor-language">
      <k-icon type="code" />
      <k-input
        :empty="false"
        :options="languages"
        :value="content.language"
        type="select"
        @input="update({ language: $event })"
      />
    </div>
  </div>
</div>
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    content: [Array, Object],
    fieldset: Object
  },
  computed: {
    languages() {
      let languages = null;

      Object.values(this.fieldset.tabs).forEach(tab => {
        if (tab.fields.language) {
          languages = tab.fields.language;
        }
      });

      return languages.options;
    }
  },
  methods: {
    update(value) {
      this.$emit("update", {
        ...this.content,
        ...value
      });
    }
  }
};
</script>

<style lang="scss">
.k-block-code {
  padding: 1.5rem 0;
}
.k-block-code-editor {
  position: relative;
  font-size: $text-sm;
  line-height: 1.5em;
  background: #000;
  border-radius: $rounded;
  padding: 1.5rem;
  color: #fff;
  font-family: $font-mono;
}
.k-block-code-editor .k-editor {
  white-space: pre-wrap;
  line-height: 1.75em;
}
.k-block-code-editor-language {
  font-size: $text-sm;
  position: absolute;
  right: 0;
  bottom: 0;
}
.k-block-code-editor-language .k-icon {
  position: absolute;
  top: 0;
  left: 0;
  height: 1.5rem;
  display: flex;
  width: 2rem;
  z-index: 0;
}
.k-block-code-editor-language .k-select-input {
  position: relative;
  padding: .325rem .75rem .5rem 2rem;
  z-index: 1;
  font-size: $text-xs;
}
</style>
