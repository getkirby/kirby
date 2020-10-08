<template>
  <label :for="input" class="k-label">
    {{ text || " " }}
    <abbr v-if="required" :title="$t('field.required')">*</abbr>
    <k-icon
      v-if="indicator"
      :data-type="indicator.type"
      :title="indicator.title"
      type="circle"
      class="k-label-indicator"
    />
  </label>
</template>

<script>
export default {
  props: {
    field: String,
    input: [String, Number],
    required: Boolean,
    text: String,
  },
  computed: {
    hasChanges() {
      return Object.keys(this.$store.getters['content/changes']()).includes(this.field);
    },
    indicator() {
      if (this.hasChanges) {
        return {
          type: "changes",
          title: this.$t('lock.unsaved')
        };
      }

      return false;
    }
  }
}
</script>

<style lang="scss">
.k-label {
  font-weight: $font-bold;
  display: flex;
  align-items: center;
  padding: 0 0 0.75rem;
  flex-grow: 1;
  line-height: 1.25rem;
}
.k-label abbr {
  text-decoration: none;
  color: $color-light-grey;
  padding-left: 0.25rem;
}
.k-label-indicator {
  margin-left: .2rem;
  transform: scale(0.5);

  &[data-type="changes"] {
    color: $color-notice;
  }
}
</style>
