<template>
  <div class="k-block-type-code-editor">
    <k-input
      ref="code"
      :buttons="false"
      :placeholder="placeholder"
      :spellcheck="false"
      :value="content.code"
      type="textarea"
      @input="update({ code: $event })"
    />
    <div v-if="languages.length" class="k-block-type-code-editor-language">
      <k-icon type="code" />
      <k-input
        ref="language"
        :empty="false"
        :options="languages"
        :value="content.language"
        type="select"
        @input="update({ language: $event })"
      />
    </div>
  </div>
</template>

<script>
/**
 * @displayName BlockTypeCode
 * @internal
 */
export default {
  computed: {
    placeholder() {
      return this.field("code", {}).placeholder;
    },
    languages() {
      return this.field("language", { options: [] }).options;
    }
  },
  methods: {
    focus() {
      this.$refs.code.focus();
    }
  }
};
</script>

<style>
.k-block-type-code-editor {
  position: relative;
  font-size: var(--text-sm);
  line-height: 1.5em;
  background: #000;
  border-radius: var(--rounded);
  padding: .5rem .75rem 3rem;
  color: #fff;
  font-family: var(--font-mono);
}
.k-block-type-code-editor .k-editor {
  white-space: pre-wrap;
  line-height: 1.75em;
}
.k-block-type-code-editor-language {
  font-size: var(--text-sm);
  position: absolute;
  right: 0;
  bottom: 0;
}
.k-block-type-code-editor-language .k-icon {
  position: absolute;
  top: 0;
  left: 0;
  height: 1.5rem;
  display: flex;
  width: 2rem;
  z-index: 0;
}
.k-block-type-code-editor-language .k-select-input {
  position: relative;
  padding: .325rem .75rem .5rem 2rem;
  z-index: 1;
  font-size: var(--text-xs);
}
</style>
