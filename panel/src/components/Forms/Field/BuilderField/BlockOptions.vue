<template>
  <k-dropdown class="k-block-options">
    <k-sort-handle class="k-block-handle" />
    <k-button
      class="k-block-options-toggle"
      icon="angle-down"
      @click="$refs.options.toggle()"
    />
    <k-dropdown-content ref="options" align="left">
      <k-dropdown-item :disabled="isFull" icon="angle-up" @click="$emit('prepend')">
        {{ $t("insert.before") }}
      </k-dropdown-item>
      <k-dropdown-item :disabled="isFull" icon="angle-down" @click="$emit('append')">
        {{ $t("insert.after") }}
      </k-dropdown-item>
      <hr>
      <k-dropdown-item :icon="isHidden ? 'preview' : 'hidden'" @click="$emit(isHidden ? 'show' : 'hide')">
        {{ isHidden === true ? $t('show') : $t('hide') }}
      </k-dropdown-item>
      <k-dropdown-item :disabled="isFull" icon="copy" @click="$emit('duplicate')">
        {{ $t("duplicate") }}
      </k-dropdown-item>
      <hr>
      <k-dropdown-item icon="trash" @click="$emit('remove')">
        {{ $t("delete") }}
      </k-dropdown-item>
    </k-dropdown-content>
  </k-dropdown>
</template>

<script>
export default {
  props: {
    isFull: Boolean,
    isHidden: Boolean,
  }
};
</script>

<style lang="scss">
.k-block-options {
  display: flex;
  align-items: center;
}
.k-block-handle.k-sort-handle,
.k-block-options-toggle {
  width: 1.5rem;
  height: 1.5rem;
  color: $color-gray-500;
}
.k-block-handle.k-sort-handle:hover,
.k-block-options-toggle:hover {
  background: $color-background;
}
</style>

