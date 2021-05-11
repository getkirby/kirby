<template>
  <k-dropdown class="k-block-options">
    <template v-if="isBatched">
      <k-button
        :tooltip="$t('remove')"
        class="k-block-options-button"
        icon="trash"
        @mousedown.native.prevent="$emit('confirmToRemoveSelected')"
      />
    </template>
    <template v-else>
      <k-button
        :tooltip="$t('edit')"
        icon="edit"
        class="k-block-options-button"
        @click="$emit('open')"
      />
      <k-button
        :disabled="isFull"
        :tooltip="$t('insert.after')"
        class="k-block-options-button"
        icon="add"
        @click="$emit('chooseToAppend')"
      />
      <k-button
        :tooltip="$t('delete')"
        class="k-block-options-button"
        icon="trash"
        @click="$emit('confirmToRemove')"
      />
      <k-button
        :tooltip="$t('more')"
        class="k-block-options-button"
        icon="dots"
        @click="$refs.options.toggle()"
      />
      <k-button
        :tooltip="$t('sort')"
        class="k-block-options-button k-block-handle"
        icon="sort"
        @keydown.up.prevent="$emit('sortUp')"
        @keydown.down.prevent="$emit('sortDown')"
      />
      <k-dropdown-content
        ref="options"
        align="right"
      >
        <k-dropdown-item :disabled="isFull" icon="angle-up" @click="$emit('chooseToPrepend')">
          {{ $t("insert.before") }}
        </k-dropdown-item>
        <k-dropdown-item :disabled="isFull" icon="angle-down" @click="$emit('chooseToAppend')">
          {{ $t("insert.after") }}
        </k-dropdown-item>
        <hr>
        <k-dropdown-item icon="edit" @click="$emit('open')">
          {{ $t("edit") }}
        </k-dropdown-item>
        <k-dropdown-item icon="refresh" @click="$emit('chooseToConvert')">
          {{ $t("field.blocks.changeType") }}
        </k-dropdown-item>
        <hr>
        <k-dropdown-item :icon="isHidden ? 'preview' : 'hidden'" @click="$emit(isHidden ? 'show' : 'hide')">
          {{ isHidden === true ? $t('show') : $t('hide') }}
        </k-dropdown-item>
        <k-dropdown-item :disabled="isFull" icon="copy" @click="$emit('duplicate')">
          {{ $t("duplicate") }}
        </k-dropdown-item>
        <hr>
        <k-dropdown-item icon="trash" @click="$emit('confirmToRemove')">
          {{ $t("delete") }}
        </k-dropdown-item>
      </k-dropdown-content>
    </template>
  </k-dropdown>
</template>

<script>
/**
 * @internal
 */
export default {
  props: {
    isBatched: Boolean,
    isFull: Boolean,
    isHidden: Boolean,
  },
  methods: {
    open() {
      this.$refs.options.open();
    }
  }
};
</script>

<style lang="scss">
$block-options-button-size: 30px;

.k-block-options {
  display: flex;
  align-items: center;
  background: $color-white;
  z-index: z-index(dropdown);
  box-shadow: rgba(#000, .1) -2px 0 5px, $shadow, $shadow-xl;
  color: $color-black;
  border-radius: $rounded;
}
.k-block-options-button {
  width: $block-options-button-size;
  height: $block-options-button-size;
  line-height: 1;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-right: 1px solid $color-background;
}
.k-block-options-button:first-child {
  border-top-left-radius: $rounded;
  border-bottom-left-radius: $rounded;
}
.k-block-options-button:last-child {
  border-top-right-radius: $rounded;
  border-bottom-right-radius: $rounded;
}
.k-block-options-button:last-of-type {
  border-right: 0;
}
.k-block-options-button[aria-current] {
  color: $color-focus;
}
.k-block-options-button:hover {
  background: $color-gray-100;
}
.k-block-options .k-dropdown-content {
  margin-top: .5rem;
}
</style>

