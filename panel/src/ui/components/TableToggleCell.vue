<template>
  <div class="flex justify-center">
    <k-toggle-input
      :value="value"
      :text="false"
      v-on="$listeners"
    />
  </div>
</template>

<script>
  export default {
    props: {
      column: {
        type: Object,
        default() {
          return {};
        }
      },
      value: [Boolean, Number, String],
    },
  }
</script>

