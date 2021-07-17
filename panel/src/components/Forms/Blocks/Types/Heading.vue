<template>
  <div :data-level="content.level" class="k-block-type-heading-input">
    <k-writer
      ref="input"
      :inline="true"
      :marks="textField.marks"
      :placeholder="textField.placeholder"
      :value="content.text"
      @input="update({ text: $event })"
    />
  </div>
</template>

<script>
/**
 * @displayName BlockTypeHeading
 * @internal
 */
export default {
  computed: {
    textField() {
      return this.field("text", {
        marks: true
      });
    }
  },
  methods: {
    focus() {
      this.$refs.input.focus();
    }
  }
};
</script>

<style>
.k-block-type-heading-input {
  line-height: 1.25em;
  font-weight: var(--font-bold);
}
.k-block-type-heading-input[data-level="h1"] {
  font-size: var(--text-3xl);
  line-height: 1.125em;
}
.k-block-type-heading-input[data-level="h2"] {
  font-size: var(--text-2xl);
}
.k-block-type-heading-input[data-level="h3"] {
  font-size: var(--text-xl);
}
.k-block-type-heading-input[data-level="h4"] {
  font-size: var(--text-lg);
}
.k-block-type-heading-input[data-level="h5"] {
  line-height: 1.5em;
  font-size: var(--text-base);
}
.k-block-type-heading-input[data-level="h6"] {
  line-height: 1.5em;
  font-size: var(--text-sm);
}
.k-block-type-heading-input .ProseMirror strong {
  font-weight: 700;
}
</style>
