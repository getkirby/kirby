<template>
  <k-dialog
    ref="dialog"
    :cancel-button="false"
    :submit-button="false"
    class="k-block-selector"
    size="medium"
  >
    <k-headline v-if="headline">
      {{ headline }}
    </k-headline>
    <details
      v-for="(group, groupName) in groups"
      :key="groupName"
      :open="group.open"
    >
      <summary>{{ group.label }}</summary>
      <div class="k-block-types">
        <k-button
          v-for="fieldset in group.fieldsets"
          :ref="'fieldset-' + fieldset.index"
          :key="fieldset.name"
          :disabled="disabled.includes(fieldset.type)"
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
/**
 * @internal
 */
export default {
  inheritAttrs: false,
  props: {
    endpoint: String,
    fieldsets: Object,
    fieldsetGroups: Object
  },
  data() {
    return {
      disabled: [],
      headline: null,
      payload: null,
      event: "add",
      groups: this.createGroups()
    }
  },
  methods: {
    add(type) {
      this.$emit(this.event, type, this.payload);
      this.$refs.dialog.close();
    },
    createGroups() {
      let groups = {};
      let index = 0;

      const fieldsetGroups = this.fieldsetGroups || {
        blocks: {
          label: this.$t('field.blocks.fieldsets.label'),
          sets: Object.keys(this.fieldsets),
        }
      };

      Object.keys(fieldsetGroups).forEach(key => {
        let group = fieldsetGroups[key];

        group.open = group.open === false ? false : true;
        group.fieldsets = group.sets.filter(fieldsetName => this.fieldsets[fieldsetName]).map(fieldsetName => {
          index++;

          return {
            ...this.fieldsets[fieldsetName],
            index
          };
        });

        if (group.fieldsets.length === 0) {
          return;
        }

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
    open(payload, params = {}) {
      const options = {
        event: "add",
        disabled: [],
        headline: null,
        ...params
      };

      this.event    = options.event;
      this.disabled = options.disabled;
      this.headline = options.headline;
      this.payload  = payload;
      this.$refs.dialog.open();
    },
  }
};
</script>

<style>
.k-block-selector.k-dialog {
  background: #313740;
  color: var(--color-white);
}
.k-block-selector .k-headline {
  margin-bottom: 1rem;
}
.k-block-selector details:not(:last-of-type) {
  margin-bottom: 1.5rem;
}
.k-block-selector summary {
  font-size: var(--text-xs);
  cursor: pointer;
  color: var(--color-gray-400);
}
.k-block-selector details:only-child summary {
  pointer-events: none;
}
.k-block-selector summary:focus {
  outline: 0;
}
.k-block-selector summary:focus-visible {
  color: var(--color-green-400);
}
.k-block-types {
  display: grid;
  grid-gap: 2px;
  margin-top: .75rem;
  grid-template-columns: repeat(1, 1fr);
}
.k-block-types .k-button {
  display: flex;
  align-items: top;
  background: rgba(0, 0, 0, .5);
  width: 100%;
  text-align: left;
  padding: 0 .75rem 0 0;
  line-height: 1.5em;
}
.k-block-types .k-button:focus {
  outline: 2px solid var(--color-green-300);
}
.k-block-types .k-button .k-button-text {
  padding: .5rem 0 .5rem .5rem;
}
.k-block-types .k-button .k-icon {
  width: 38px;
  height: 38px;
}
</style>
