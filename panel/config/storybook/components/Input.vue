<template>
  <div>
    <slot />
    <br>
    <br>
    <k-headline style="margin-bottom: .75rem">Value</k-headline>
    <k-code-block :code="value" />
  </div>
</template>

<script>

export default {
  props: ["value"]
}
</script>
