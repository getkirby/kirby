<template>
  <k-inside
    class="k-settings-view"
    view="settings"
  >
    <k-view>
      <k-header>
        {{ $t('view.settings') }}
      </k-header>

      <!-- system -->
      <k-system v-bind="$props" @update="$emit('update')" />

      <!-- languages -->
      <k-languages v-if="multilang" />
    </k-view>
  </k-inside>
</template>

<script>
export default {
  props: {
    license: String,
    multilang: Boolean,
    update: [Object, Boolean],
    version: String,
  }
};
</script>
