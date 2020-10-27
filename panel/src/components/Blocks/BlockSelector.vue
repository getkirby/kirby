<template>
  <k-dialog
    ref="dialog"
    :cancel-button="false"
    :submit-button="false"
    class="k-block-selector"
    size="medium"
  >

    <details open v-for="(group, groupName) in groups" :key="groupName">
      <summary>{{ group.label }}</summary>
      <div class="k-block-types">
        <k-button
          v-for="(fieldset, index) in group.fieldsets"
          :ref="'fieldset-' + fieldset.index"
          :key="fieldset.name"
          :icon="fieldset.icon || 'box'"
          @keydown.up="navigate(fieldset.index - 1)"
          @keydown.down="navigate(fieldset.index + 1)"
          @click="add(fieldset.type)"
        >
          {{ fieldset.name }}
        </k-button>
      </div>
    </details>

  </k-dialog>
</template>

<script>
export default {
  inheritAttrs: false,
  props: {
    endpoint: String,
    fieldsets: Object,
    fieldsetGroups: Object
  },
  data() {
    return {
      index: 0,
      groups: this.createGroups()
    }
  },
  methods: {
    add(type) {
      this.$emit("add", type, this.index);
      this.$refs.dialog.close();
    },
    createGroups() {
      let groups = {};
      let index = 0;

      const fieldsetGroups = this.fieldsetGroups || {
        blocks: {
          label: this.$t('field.blocks.fieldsets.label'),
          fieldsets: Object.keys(this.fieldsets),
        }
      };

      Object.keys(fieldsetGroups).forEach(key => {
        let group = fieldsetGroups[key];

        group.fieldsets = group.fieldsets.filter(fieldsetName => this.fieldsets[fieldsetName]).map(fieldsetName => {
          index++;

          return {
            ...this.fieldsets[fieldsetName],
            index
          };
        });

        groups[key] = group;
      });

      return groups;
    },
    navigate(index) {
      const ref = this.$refs["fieldset-" + index];

      if (ref && ref[0]) {
        ref[0].focus();
      }
    },
    open(index) {
      this.index = index;
      this.$refs.dialog.open();
    },
  }
};
</script>

<style lang="scss">
.k-block-selector.k-dialog {
  background: #313740;
  color: $color-white;
}
.k-block-selector details:not(:last-of-type) {
  margin-bottom: 1.5rem;
}
.k-block-selector summary {
  font-size: $text-xs;
  cursor: pointer;
  color: $color-gray-400;
}
.k-block-selector details:only-child summary {
  pointer-events: none;
}
.k-block-selector summary:focus {
  outline: 0;
}
.k-block-selector summary:focus-visible {
  color: $color-green-400;
}
.k-block-types {
  display: grid;
  grid-gap: 2px;
  margin-top: .75rem;
  grid-template-columns: repeat(1, 1fr);
}
.k-block-types .k-button {
  display: grid;
  grid-template-columns: 2rem 1fr;
  align-items: top;
  background: rgba(#000, .5);
  width: 100%;
  text-align: left;
  padding: 0 .75rem 0 0;
  line-height: 1.5em;
}
.k-block-types .k-button:focus {
  outline: 2px solid $color-green-300;
}
.k-block-types .k-button .k-button-text {
  padding: .5rem 0 .5rem .5rem;
}
.k-block-types .k-button .k-icon {
  width: 38px;
  height: 38px;
}
</style>
