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
/**
 * @example <k-info-field label="Info" text="This is a nice info text" />
 */
export default {
  props: {
    help: String,
    label: String,
    /**
     * Sets the info text. You can use HTML here to format the info.
     */
    text: String,
    /**
     * The info box can have different styles/themes. 
     * @values The themes are inherited from the `<k-box>` component.
     */
    theme: {
      type: String,
      default: "info"
    }
  }
};
</script>

<style>
.k-info-field .k-headline {
  padding-bottom: .75rem;
  line-height: 1.25rem;
}
</style>
