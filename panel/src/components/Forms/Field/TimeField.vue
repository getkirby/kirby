<template>
  <k-field :input="_uid" v-bind="$props" class="k-time-field">
    <k-input
      :id="_uid"
      ref="input"
      v-bind="$props"
      theme="field"
      type="time"
      @input="$emit('input', $event || '')"
    >
      <template v-if="times" #icon>
        <k-dropdown>
          <k-button
            :icon="icon || 'clock'"
            :tooltip="$t('time.select')"
            class="k-input-icon-button"
            @click="$refs.times.toggle()"
          />
          <k-dropdown-content ref="times" align="right">
            <k-times :display="display" :value="value" @input="select" />
          </k-dropdown-content>
        </k-dropdown>
      </template>
    </k-input>
  </k-field>
</template>

<script>
import { props as Field } from "../Field.vue";
import { props as Input } from "../Input.vue";
import { props as TimeInput } from "../Input/TimeInput.vue";

/**
 * Form field to handle a time value.
 *
 * Have a look at `<k-field>`, `<k-input>`
 * and `<k-time-input>` for additional information.
 *
 * @example <k-time-field v-model="time" name="time" label="Time" />
 * @public
 */
export default {
  mixins: [Field, Input, TimeInput],
  inheritAttrs: false,
  props: {
    icon: {
      type: String,
      default: "clock"
    },
    /**
     * Deactivate the dropdown timer or not
     */
    times: {
      type: Boolean,
      default: true
    }
  },
  methods: {
    /**
     * Focuses the input element
     * @public
     */
    focus() {
      this.$refs.input.focus();
    },
    select(value) {
      this.$emit("input", value);
      this.$refs.times?.close();
    }
  }
};
</script>
