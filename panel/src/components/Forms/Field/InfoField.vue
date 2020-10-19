<template>
  <div class="k-field k-info-field">
    <k-headline>{{ label }}</k-headline>
    <k-box :theme="theme">
      <k-text v-html="text" />
    </k-box>
    <footer v-if="help" class="k-field-footer">
      <k-text
        v-if="help"
        theme="help"
        class="k-field-help"
        v-html="help"
      />
    </footer>
  </div>
</template>

<script>
export default {
  props: {
    help: String,
    label: String,
    text: String,
    theme: {
      type: String,
      default: "info"
    }
  }
};
</script>

<style lang="scss">

.k-info-field .k-headline {
  padding-bottom: .75rem;
  line-height: 1.25rem;
}

</style>
