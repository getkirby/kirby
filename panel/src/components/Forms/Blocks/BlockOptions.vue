<template>
  <k-dropdown class="k-block-options">
    <template v-if="isBatched">
      <k-button
        :tooltip="$t('copy')"
        class="k-block-options-button"
        icon="template"
        @click.prevent="$emit('copy')"
      />
      <k-button
        :tooltip="$t('remove')"
        class="k-block-options-button"
        icon="trash"
        @click.prevent="$emit('confirmToRemoveSelected')"
      />
    </template>
    <template v-else>
      <k-button
        v-if="isEditable"
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
        class="k-block-options-button k-sort-handle"
        icon="sort"
        @keydown.up.prevent="$emit('sortUp')"
        @keydown.down.prevent="$emit('sortDown')"
      />
      <k-dropdown-content ref="options" align="right">
        <k-dropdown-item
          :disabled="isFull"
          icon="angle-up"
          @click="$emit('chooseToPrepend')"
        >
          {{ $t("insert.before") }}
        </k-dropdown-item>
        <k-dropdown-item
          :disabled="isFull"
          icon="angle-down"
          @click="$emit('chooseToAppend')"
        >
          {{ $t("insert.after") }}
        </k-dropdown-item>
        <hr />
        <k-dropdown-item v-if="isEditable" icon="edit" @click="$emit('open')">
          {{ $t("edit") }}
        </k-dropdown-item>
        <k-dropdown-item icon="refresh" @click="$emit('chooseToConvert')">
          {{ $t("field.blocks.changeType") }}
        </k-dropdown-item>
        <hr />
        <k-dropdown-item icon="template" @click="$emit('copy')">
          {{ $t("copy") }}
        </k-dropdown-item>
        <k-dropdown-item icon="download" @click="$emit('paste')">
          {{ $t("paste.after") }}
        </k-dropdown-item>
        <hr />
        <k-dropdown-item
          :icon="isHidden ? 'preview' : 'hidden'"
          @click="$emit(isHidden ? 'show' : 'hide')"
        >
          {{ isHidden === true ? $t("show") : $t("hide") }}
        </k-dropdown-item>
        <k-dropdown-item
          :disabled="isFull"
          icon="copy"
          @click="$emit('duplicate')"
        >
          {{ $t("duplicate") }}
        </k-dropdown-item>
        <hr />
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
    isEditable: Boolean,
    isFull: Boolean,
    isHidden: Boolean
  },
  methods: {
    open() {
      this.$refs.options.open();
    }
  }
};
</script>

<style>
.k-block-options {
  display: flex;
  align-items: center;
  background: var(--color-white);
  z-index: var(--z-dropdown);
  box-shadow: rgba(0, 0, 0, 0.1) -2px 0 5px, var(--shadow), var(--shadow-xl);
  color: var(--color-black);
  border-radius: var(--rounded);
}
.k-block-options-button {
  --block-options-button-size: 30px;

  width: var(--block-options-button-size);
  height: var(--block-options-button-size);
  line-height: 1;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-inline-end: 1px solid var(--color-background);
}
.k-block-options-button:first-child {
  border-start-start-radius: var(--rounded);
  border-end-start-radius: var(--rounded);
}
.k-block-options-button:last-child {
  border-start-end-radius: var(--rounded);
  border-end-end-radius: var(--rounded);
}
.k-block-options-button:last-of-type {
  border-inline-end: 0;
}
.k-block-options-button[aria-current] {
  color: var(--color-focus);
}
.k-block-options-button:hover {
  background: var(--color-gray-100);
}
.k-block-options .k-dropdown-content {
  margin-top: 0.5rem;
}
</style>
