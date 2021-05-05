<template>
  <div class="k-block-title" v-on="$listeners">
    <k-icon :type="icon" class="k-block-icon" />
    <span class="k-block-name">
      {{ name }}
    </span>
    <span v-if="label" class="k-block-label">
      {{ label }}
    </span>
  </div>
</template>

<script>
/**
 * @internal
 */
export default {
  inheritAttrs: false,
  props: {
    fieldset: Object,
    content: Object
  },
  computed: {
    icon() {
      return this.fieldset.icon || "box";
    },
    label() {
      if (!this.fieldset.label || this.fieldset.label.length === 0) {
        return false;
      }

      if (this.fieldset.label === this.fieldset.name) {
        return false;
      }

      const label = this.$helper.string.template(this.fieldset.label, this.content);
      return label === "…" ? false : label;
    },
    name() {
      return this.fieldset.name;
    }
  }
};
</script>

<style>
.k-block-title {
  display: flex;
  align-items: center;
  min-width: 0;
  padding-right: .75rem;
  font-size: var(--text-sm);
  line-height: 1;
}
.k-block-icon {
  width: 1rem;
  margin-right: .5rem;
  color: var(--color-gray-500);
}
.k-block-name {
  margin-right: .5rem;
}
.k-block-label {
  color: var(--color-gray-600);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
