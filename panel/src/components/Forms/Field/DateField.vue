<template>
  <k-field
    :input="_uid"
    v-bind="$props"
    class="k-date-field"
    @focusout.native="onBlur"
  >
    <k-input
      :id="_uid"
      ref="input"
      :type="inputType"
      :value="value"
      v-bind="$props"
      theme="field"
      @blur="onBlur"
      @enter="onSelect"
      @focus="onFocus"
      @input="onInput"
      @update="onUpdate"
    >
      <template v-if="calendar" #icon>
        <k-dropdown>
          <k-button
            :icon="icon"
            :tooltip="$t('date.select')"
            class="k-input-icon-button"
            tabindex="-1"
            @click="$refs.calendar.toggle()"
          />
          <k-dropdown-content ref="calendar" align="right">
            <k-calendar
              :value="datetime"
              :min="min"
              :max="max"
              @input="onUpdate"
            />
          </k-dropdown-content>
        </k-dropdown>
      </template>
    </k-input>
  </k-field>
</template>

<script>
import { props as Field } from "../Field.vue";
import { props as Input } from "../Input.vue";
import { props as DateTimeInput } from "../Input/DateTimeInput.vue";

/**
 * Have a look at `<k-field>`, `<k-input>` and `<k-datetime-input>` for additional information.
 * @example <k-date-field v-model="date" name="date" label="Date" />
 */
export default {
  mixins: [Field, Input, DateTimeInput],
  inheritAttrs: false,
  props: {
    /**
     * Deactivate the dropdown calendar or not
     */
    calendar: {
      type: Boolean,
      default: true
    },
    icon: {
      type: String,
      default: "calendar"
    }
  },
  data() {
    return {
      // ISO string - we need to hold on to an temporary
      // value, so that we can pass the temporary input event values
      // on to the calendar component without updating the content value
      // (we will update only on the update event)
      datetime: this.value
    };
  },
  computed: {
    inputType() {
      return this.time === false ? "date" : "datetime";
    }
  },
  watch: {
    value(value) {
      this.datetime = value;
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
    /**
     * Closes calendar when input is blured
     */
    onBlur(e) {
      if (!e || this.$el.contains(e.relatedTarget) === false) {
        this.$refs.calendar?.close();
      }
    },
    /**
     * Open calendar when input is focussed
     */
    onFocus() {
      this.$refs.calendar?.open();
    },
    /**
     * Update the content value by
     * emitting the input event
     */
    onUpdate(value) {
      this.$emit("input", value);
    },
    /**
     * Store temporary value to be
     * shared between input and calendar
     */
    onInput(value) {
      this.datetime = value;
    },
    /**
     * Update value and close calendar
     */
    onSelect(value) {
      this.onUpdate(value);
      this.onBlur();
    }
  }
};
</script>
