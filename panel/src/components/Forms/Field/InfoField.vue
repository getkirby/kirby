<template>
  <div class="k-field k-info-field">
    <k-headline>{{ label }}</k-headline>
    <k-box :theme="theme">
      <!-- eslint-disable-next-line vue/no-v-html -->
      <k-text v-html="text" />
    </k-box>
    <footer v-if="help" class="k-field-footer">
      <!-- eslint-disable vue/no-v-html -->
      <k-text
        v-if="help"
        theme="help"
        class="k-field-help"
        v-html="help"
      />
      <!-- eslint-enable vue/no-v-html -->
    </footer>
  </div>
</template>

<script>
import {
  help,
  label
} from "@/mixins/props.js";

export default {
  mixins: [
    help,
    label
  ],
  props: {
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
