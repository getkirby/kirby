<template>
  <k-block-title
    :content="content"
    :fieldset="fieldset"
    @dblclick="$emit('open')"
  />
</template>

<script>
/**
 * @displayName BlockTypeDefault
 * @internal
 */
export default {
  extends: "k-block-type"
}
</script>

<style>
.k-block-type-default .k-block-title {
  line-height: 1.5em;
}
</style>
